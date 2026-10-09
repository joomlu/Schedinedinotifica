# Accesso SSH dal Mac con chiave autorizzata — 9 ottobre 2026

Preparazione locale, **nessuna modifica del server**. Destinatario `root@217.174.149.23:6543`. Il tentativo precedente senza identità selezionata era negato; non dimostra che questa specifica chiave sia rifiutata o autorizzata. Nessuna nuova autenticazione server eseguita in questa fase.

## Evidenza sul Mac

In `~/.ssh` ora esistono `tanggo_spanel` (permessi600) e `tanggo_spanel.pub` (644), oltre known_hosts. Nessun `~/.ssh/config` presente. La chiave privata è stata rilevata solo tramite metadati del file: contenuto mai mostrato o copiato. L'agente esistente non contiene identità (`The agent has no identities`).

Impronta della **chiave pubblica utente**, ED25519/256bit:

`SHA256:CTwvC9v41u3VbBq0el3Qyp7yDZGbmKfIQaTC8JyqiqU`

`ssh -G` conferma che con selezione esplicita il client usa root, IP217.174.149.23, porta6543 e il file indicato; non è una prova di autenticazione. Nel comando seguente la verifica della chiave host è esplicitamente obbligatoria.

## Procedura da eseguire sul Mac, senza segreti in chat

1. Verificare l'impronta della chiave pubblica sopra tramite `ssh-keygen -lf ~/.ssh/tanggo_spanel.pub`. Il responsabile dell'accesso deve confermare tramite canale fidato che **quella chiave pubblica** sia già autorizzata per root sul destinatario. Non trasmettere il file senza suffisso.pub. Se l'autorizzazione non esiste, resta un'attività server separata: non eseguita né autorizzata da questa preparazione.
2. Caricare la chiave nell'agente della propria sessione, inserendo l'eventuale passphrase soltanto nel terminale locale:

```sh
ssh-add ~/.ssh/tanggo_spanel
ssh-add -l
```

3. Confrontare l'impronta host del destinatario con una fonte indipendente affidabile. La presenza in known_hosts non sostituisce tale verifica. Per leggere l'impronta locale registrata:

```sh
ssh-keygen -F '[217.174.149.23]:6543' -f ~/.ssh/known_hosts | ssh-keygen -lf -
```

4. Quando l'identità è pronta, verificare l'autenticazione con comando remoto senza effetti applicativi:

```sh
ssh -p 6543 -i ~/.ssh/tanggo_spanel \
  -o IdentitiesOnly=yes -o BatchMode=yes \
  -o StrictHostKeyChecking=yes -o ConnectTimeout=10 \
  root@217.174.149.23 true
```

Exit0 chiude soltanto l'autenticazione; successivamente restano le letture autorizzate CLI/FPM/MariaDB/journal/metadati migration. Se manca la chiave host o cambia l'impronta, interrompere e verificarla indipendentemente. Non usare StrictHostKeyChecking=no né sostituire known_hosts per superare l'errore.

Nessun ssh-copy-id, modifica authorized_keys/sshd, chmod server, riavvio, deploy o query applicativa in questa preparazione. Nessuna chiave privata, passphrase o password deve essere inviata in chat. Il caricamento nell'agente proposto è locale e non installa la chiave sul server.

## Nuovo tentativo con identità esplicita — 9 ottobre 2026

Tentativo autorizzato root@217.174.149.23:6543, BatchMode=yes/IdentitiesOnly=yes/StrictHostKeyChecking=yes, comando remoto `true`. Exit255: `Permission denied (publickey,gssapi-keyex,gssapi-with-mic,password)`. Diagnostico selettivo conferma **Offering public key** con l'impronta sopra, senza **Server accepts key**. Nessun comando remoto eseguito; chiave privata non letta/esposta e server non modificato.

L'agente vuoto non spiega da solo il rifiuto: il server non accetta la chiave pubblica offerta. Passo manuale concreto: tramite consoleSPanel o amministratore su canale fidato, verificare che la chiave **tanggo_spanel.pub**, impronta CTwvC9v41u3VbBq0el3Qyp7yDZGbmKfIQaTC8JyqiqU, sia autorizzata per **root sul servizioSSH6543**, e che la policy consenta quel login. Non installare/modificare chiavi sul server sotto l'autorizzazione attuale; se manca, occorre esplicita autorizzazione a quell'intervento. Il comando di verifica delle impronte locali è `ssh-keygen -lf ~/.ssh/tanggo_spanel.pub`.

Se il server accetta la chiave ma la firma locale richiede sblocco, eseguire dal proprio terminale `ssh-add ~/.ssh/tanggo_spanel` e inserire lì l'eventuale passphrase, quindi ripetere il comandoSSH documentato. Oggi non si è raggiunta questa fase. Non chiedere o trasmettere chiavi private/password in chat. CLI/FPM/MariaDB/segfault/migrationmetadata/backup rimangono non verificabili finché il login non passa.


## Comando richiesto per la sessione autenticata

[Comando esatto con chiave pubblica](SSH-COMANDO-CHIAVE-ROOT-2026-10-09.md), da eseguire dall’utente come root: copiaauthorized_keys, append solo se assente, nessuna modifica policySSH. Non eseguito dall’assistente sulserver.
