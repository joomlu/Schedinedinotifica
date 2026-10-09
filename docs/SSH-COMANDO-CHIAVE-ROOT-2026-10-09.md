# Comando manuale per autorizzare la chiave pubblica — 9 ottobre 2026

Da eseguire **dall'utente nella propria sessione autenticata come root sul destinatario217.174.149.23**. L'assistente non ha scritto sul server. Il comando conserva il file attuale in una copia univoca, evita duplicati anche se la chiave esiste con opzioni/restrizioni e non modifica sshd, policyroot, porte o configurazioneSPanel. Si riferisce al percorso standard `/root/.ssh/authorized_keys`: se il servizio6543 usa un percorso differente, identificarlo prima e non applicare questo comando alla cieca. Un link simbolico fa interrompere il comando senza modificarlo.

```sh
(
  set -eu
  test "$(id -u)" -eq 0
  umask 077
  mkdir -p /root/.ssh
  chmod 700 /root/.ssh
  SCHEDINE_AK=/root/.ssh/authorized_keys
  test ! -L "$SCHEDINE_AK"
  test -e "$SCHEDINE_AK" || touch "$SCHEDINE_AK"
  cp -p "$SCHEDINE_AK" "$(mktemp /root/.ssh/authorized_keys.pre-schedine.XXXXXX)"
  SCHEDINE_PUB='ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAILYjlge6o7TNtoF9iQ8DDo/Ew6auVyomn+Vsnbso/pVT'
  grep -Fq -- "$SCHEDINE_PUB" "$SCHEDINE_AK" || printf '\n%s\n' "$SCHEDINE_PUB" >> "$SCHEDINE_AK"
  chmod 600 "$SCHEDINE_AK"
  ssh-keygen -lf "$SCHEDINE_AK" | grep -F 'SHA256:CTwvC9v41u3VbBq0el3Qyp7yDZGbmKfIQaTC8JyqiqU'
)
```

Contiene soltanto la chiave **pubblica** già identificata; nessuna chiave privata/passphrase. La presenza dell'impronta conferma il file, non che la policy del servizio accetti l'accesso. Dal Mac, dopo quel passo:

```sh
ssh -p 6543 -i ~/.ssh/tanggo_spanel \
  -o IdentitiesOnly=yes -o BatchMode=yes \
  -o StrictHostKeyChecking=yes -o ConnectTimeout=10 \
  root@217.174.149.23 true
```

Se la chiave è accettata ma richiede firma con passphrase, `ssh-add ~/.ssh/tanggo_spanel` nel terminaleMac e passphrase soltanto lì. Non disabilitare verifica host o modificare policy per far passare il test. Restano le letture autorizzate CLI/FPM/MariaDB/segfault/migrationmetadata/backup; nessuna verifica produttiva ottenuta dal solo inserimento della chiave.
