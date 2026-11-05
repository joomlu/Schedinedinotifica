#!/usr/bin/env python3
"""Sviluppo e controlli nella VM su Extra; nessun deploy o accesso produttivo."""
import argparse,hashlib,json,pathlib,subprocess,tarfile,tempfile,os,shlex
ROOT=pathlib.Path(__file__).resolve().parents[2]
VM='schedine-sviluppo-interno';WORK='/home/collaudo.guest/schedine-sviluppo'
PATH='/usr/local/bin:/opt/schedine-runner-runtime-Q4WIbkDa/node-v20.18.2-linux-arm64/bin:/usr/bin:/bin'
TOOLS='/opt/schedine-sviluppo/strumenti'
def guest(argv,**kw):
 disk=pathlib.Path.home()/'.lima'/VM/'disk'
 if disk.is_symlink() and not disk.exists():
  raise SystemExit('Collegare Extra e avviare la VM con: python3 scripts/development/volume-vm-esterno.py avvia')
 return subprocess.run(['limactl','shell',VM,*argv],check=True,**kw)
def source_names():
 data=subprocess.check_output(['git','ls-files','-z','--cached','--others','--exclude-standard'],cwd=ROOT)
 excluded=('.env','output/','docs/evidenze/','vendor/','node_modules/','storage/','bootstrap/cache/','public/images/','public/build/','public/site-assets/')
 return sorted({n for n in data.decode().split('\0') if n and not n.startswith(excluded) and (ROOT/n).is_file() and not (ROOT/n).is_symlink()})
def sync():
 sha=subprocess.check_output(['git','rev-parse','HEAD'],cwd=ROOT).decode().strip()
 with tempfile.TemporaryDirectory(prefix='schedine-sync-') as tmp:
  package=pathlib.Path(tmp)/'source.tar';files={}
  templates={'tests/Isolation/run.py':'run-isolato.py','tests/Isolation/database_policy.py':'database-policy.py','tests/Support/TestingEnvironment.php':'TestingEnvironment.php'}
  with tarfile.open(package,'w') as tar:
   for name in source_names():
    data=(ROOT/name).read_bytes()
    if name in templates:data=(ROOT/'scripts/development/templates'/templates[name]).read_bytes().replace(b'__CANDIDATO__',sha.encode())
    import io
    row=tarfile.TarInfo(name);row.size=len(data);row.mode=(ROOT/name).stat().st_mode&0o777;tar.addfile(row,io.BytesIO(data));files[name]=hashlib.sha256(data).hexdigest()
  config={'sha':sha,'files':files}
  # Nessun file manualmente cambiato nella VM viene sovrascritto.
  check="""import pathlib,json,hashlib,sys,subprocess
r=pathlib.Path(sys.argv[1]);s=json.loads((r/'.snapshot-interno.json').read_text());head=subprocess.check_output(['git','-C',str(r),'rev-parse','HEAD']).decode().strip();assert head==s['sha'],'Commit VM modificato: preservare e riconciliare prima di sincronizzare'
changed=[n for n,h in s['files'].items() if not (r/n).is_file() or hashlib.sha256((r/n).read_bytes()).hexdigest()!=h];assert not changed,'Modifiche VM da preservare: '+str(changed[:20]);print('PASS copia VM conforme alla precedente sincronizzazione')"""
  guest(['python3','-c',check,WORK])
  staged=guest(['mktemp','-d','/home/collaudo.guest/schedine-sync-XXXXXXXX'],capture_output=True,text=True).stdout.strip()
  with package.open('rb') as f:guest(['tar','-xf','-','-C',staged],stdin=f)
  apply="""import pathlib,json,sys,hashlib,shutil,time,subprocess
r=pathlib.Path(sys.argv[1]);stage=pathlib.Path(sys.argv[2]);v=json.loads(sys.stdin.read());old=json.loads((r/'.snapshot-interno.json').read_text());history=r.parent/'storia-sviluppo'/str(time.time_ns());history.mkdir(parents=True,mode=0o700)
for n,h in old['files'].items():
 if v['files'].get(n)==h:continue
 p=r/n;backup=history/n;backup.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(p,backup);assert hashlib.sha256(backup.read_bytes()).hexdigest()==h
 if n not in v['files']:p.unlink()
for n,h in v['files'].items():
 p=stage/n;assert hashlib.sha256(p.read_bytes()).hexdigest()==h;dest=r/n;dest.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(p,dest)
(r/'.snapshot-interno.json').write_text(json.dumps(v,sort_keys=True));shutil.rmtree(stage);print('PASS sincronizzazione; versioni precedenti modificate conservate in '+str(history))"""
  # Il commit nuovo richiede aggiornamento del solo Git della copia, mai checkout dei file host.
  if sha!=guest(['git','-C',WORK,'rev-parse','HEAD'],capture_output=True,text=True).stdout.strip():
   bundle=pathlib.Path(tmp)/'source.bundle';subprocess.run(['git','bundle','create',str(bundle),'HEAD'],cwd=ROOT,check=True)
   with bundle.open('rb') as f:guest(['sh','-c','cat > '+shlex.quote(staged+'/source.bundle')],stdin=f)
   guest(['git','-C',WORK,'fetch',staged+'/source.bundle','HEAD']);guest(['git','-C',WORK,'update-ref','HEAD',sha]);guest(['git','-C',WORK,'read-tree',sha])
  guest(['python3','-c',apply,WORK,staged],input=json.dumps(config).encode())
def isolated(argv):
 guest(['sudo','bash',TOOLS+'/namespace-selinux-visibile.sh','runuser','-u','collaudo','--','env','-i','HOME=/home/collaudo.guest','PATH='+PATH,'LANG=C.UTF-8',*argv])
parser=argparse.ArgumentParser(description='Ambiente quotidiano nella VM su Extra, nessuna operazione produttiva')
parser.add_argument('azione',choices=['stato','sincronizza','dipendenze','build','test','guardie','web','chiudi-web']);parser.add_argument('file',nargs='*');args=parser.parse_args()
if args.azione=='stato':
 guest(['sh','-c','hostname; df -h /; /opt/schedine-runtime-mariadb-11.8.9/rootfs/usr/sbin/mariadbd --version; php -v | head -1'])
elif args.azione=='web':
 config=pathlib.Path.home()/'.lima'/VM/'ssh.config';out=ROOT/'output/ambiente-interno';out.mkdir(parents=True,exist_ok=True,mode=0o700);sock=out/'web-ssh.sock'
 check=subprocess.run(['ssh','-F',str(config),'-S',str(sock),'-O','check','lima-'+VM],capture_output=True)
 if check.returncode:
  state=json.loads(subprocess.check_output(['limactl','list',VM,'--json']));pid=str(state['hostAgentPID']);owned=True
  for port in [8088,3308]:
   probe=subprocess.run(['lsof','-nP','-t','-iTCP:'+str(port),'-sTCP:LISTEN'],capture_output=True,text=True)
   owned=owned and probe.returncode==0 and set(probe.stdout.split())=={pid}
  if owned:
   print('Ambiente quotidiano: http://127.0.0.1:8088 — forwarding loopback gestito dalla sola VM interna');raise SystemExit(0)
  subprocess.run(['ssh','-F',str(config),'-M','-S',str(sock),'-fNT','-o','ExitOnForwardFailure=yes','-L','127.0.0.1:8088:127.0.0.1:8088','-L','127.0.0.1:3308:127.0.0.1:3308','lima-'+VM],check=True)
 print('Ambiente quotidiano: http://127.0.0.1:8088 — MariaDB locale su127.0.0.1:3308, originale MySQL3306 invariato')
elif args.azione=='chiudi-web':
 config=pathlib.Path.home()/'.lima'/VM/'ssh.config';sock=ROOT/'output/ambiente-interno/web-ssh.sock';subprocess.run(['ssh','-F',str(config),'-S',str(sock),'-O','exit','lima-'+VM],check=True)
elif args.azione=='sincronizza':sync()
elif args.azione=='dipendenze':
 guest(['env','-i','HOME=/home/collaudo.guest','PATH='+PATH,'LANG=C.UTF-8','sh','-c','cd '+WORK+' && php /opt/schedine-runner-runtime-Q4WIbkDa/composer.phar install --no-scripts --no-plugins --no-interaction --prefer-dist && npm ci --ignore-scripts --no-audit --no-fund'])
elif args.azione=='build':isolated(['sh','-c','cd '+WORK+' && npm run build'])
elif args.azione=='guardie':isolated(['sh','-c','cd '+WORK+'/tests/Isolation && python3 -m unittest test_database_policy.py test_guards.py'])
else:
 if not args.file:parser.error('Selezionare file PHPUnit pertinenti, senza suite globale implicita')
 if any(not n.startswith('tests/') or '..' in pathlib.Path(n).parts or not (ROOT/n).is_file() for n in args.file):parser.error('Sono ammessi soltanto file del progetto sotto tests/')
 isolated(['python3',WORK+'/tests/Isolation/run.py','--phpunit',*args.file])
