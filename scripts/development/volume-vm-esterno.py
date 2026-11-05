#!/usr/bin/env python3
"""Monta e avvia la VM su Extra; nessuna operazione produttiva."""
import argparse
import json
import os
from pathlib import Path
import plistlib
import stat
import subprocess

CUSTODIA = Path.home() / 'Library/Application Support/SchedineCustodia/vm-esterna-20261028'


def esegui(*args):
    return subprocess.run(args, check=True)


def configurazione():
    percorso = CUSTODIA / 'config.json'
    if not percorso.is_file():
        raise SystemExit('Configurazione locale della VM esterna assente: ripristinare la custodia.')
    return json.loads(percorso.read_text())


def verifica_volume(config):
    volume = Path(config['mount'])
    if not os.path.ismount(volume):
        raise SystemExit('Volume VM non montato. Collegare Extra ed eseguire monta oppure avvia.')
    info = plistlib.loads(subprocess.check_output(['diskutil', 'info', '-plist', str(volume)]))
    if info.get('VolumeUUID') != config['volume_uuid']:
        raise SystemExit('Identità del volume diversa da quella registrata: operazione fermata.')


def monta(config):
    if not os.path.ismount('/Volumes/extra'):
        raise SystemExit('Extra non è collegato o non è montato con il nome previsto.')
    if os.path.ismount(config['mount']):
        verifica_volume(config)
        print('Volume VM corretto già montato.')
        return
    image = Path(config['image'])
    if not image.is_dir():
        raise SystemExit('Contenitore cifrato della VM non trovato su Extra.')
    key = CUSTODIA / 'volume.key'
    if key.is_symlink() or not key.is_file():
        raise SystemExit('Chiave locale della VM assente o non valida.')
    metadata = key.stat()
    if metadata.st_uid != os.getuid() or stat.S_IMODE(metadata.st_mode) & 0o077:
        raise SystemExit('La chiave deve appartenere all’utente ed essere accessibile solo a lui.')
    risultato = subprocess.run(
        ['hdiutil', 'attach', '-stdinpass', '-owners', 'on', '-nobrowse',
         '-mountpoint', config['mount'], str(image)],
        input=key.read_bytes() + b'\0', capture_output=True,
    )
    if risultato.returncode:
        raise SystemExit(risultato.stderr.decode(errors='replace').strip())
    verifica_volume(config)
    print('Volume VM cifrato montato su Extra.')


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('azione', choices=['stato', 'monta', 'avvia', 'ferma', 'smonta'])
    args = parser.parse_args()
    config = configurazione()
    vm = config['vm']
    if args.azione in ['monta', 'avvia']:
        monta(config)
        if args.azione == 'avvia':
            disk = Path.home() / '.lima' / vm / 'disk'
            if not disk.is_symlink() or disk.resolve() != Path(config['disk']).resolve():
                raise SystemExit('Il collegamento del disco VM non corrisponde alla destinazione verificata.')
            esegui('limactl', 'start', '--tty=false', vm)
    elif args.azione == 'ferma':
        esegui('limactl', 'stop', '--tty=false', vm)
    elif args.azione == 'smonta':
        stato = json.loads(subprocess.check_output(['limactl', 'list', vm, '--json']))
        if stato.get('status') != 'Stopped':
            raise SystemExit('Fermare prima la VM con l’azione ferma; nessuno smontaggio forzato.')
        verifica_volume(config)
        esegui('hdiutil', 'detach', config['mount'])
    else:
        esegui('limactl', 'list', vm)
        if os.path.ismount(config['mount']):
            verifica_volume(config)
            esegui('df', '-h', '/', '/Volumes/extra', config['mount'])
        else:
            print('Volume VM non montato. Collegare Extra e usare avvia.')


if __name__ == '__main__':
    main()
