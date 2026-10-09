# Integrazione del candidato Laravel 12 e verifica SSH — 9 ottobre 2026

Il delta autorizzato è integrato nei quattro file sorgente/test del candidato: composer.json, composer.lock, tests/Unit/ComponentiImportFormTest.php e tests/Feature/Laravel12ResidualExposureAudit.php. Tutti i byte coincidono con il manifest del worktree già verificato: Laravel12.69.3, PHP minimo8.3, globale750/8252PASS, Chromium26/26PASS, Composer0avvisi. Nessun cambiamento ulteriore giustifica ripetere le suite. Vendor e runtime Herd rimangono11.31.0: non eseguito composer install sul gestionale operativo. Il candidato contiene il lock12; la verifica della sua installazione è quella isolata già documentata.

## Riferimento recuperabile

La cartella `evidenze/integrazione-laravel12-2026-10-09` conserva il manifest318 originale, la descrizione, una patch binaria completa rispetto a HEAD e un archivio dei318file esatti dell'indice precedente. Hash in verifica-integrazione.json. Nessun commit o snapshot commit creato. Per recuperare usare una nuova copia isolata allo stesso HEAD e applicare candidato-318.patch; non sovrascrivere il checkout corrente. L'archivio contiene soltanto sorgenti/evidenze già selezionati, nessun .env, APP_KEY, dump o chiave privata.

## SSH: connessione riuscita, autenticazione ancora rifiutata

Test effettuato dopo la conferma dell'utente: root@217.174.149.23:6543, identità ~/.ssh/tanggo_spanel, BatchMode/IdentitiesOnly/StrictHostKeyChecking attivi. Fuori sandbox la connessione è stabilita; chiave ED25519 offerta con impronta SHA256:CTwvC9v41u3VbBq0el3Qyp7yDZGbmKfIQaTC8JyqiqU. Il server non emette Server accepts key e risponde Permission denied(publickey,gssapi-keyex,gssapi-with-mic,password), exit255. Nessuna richiesta di firma: ssh-add non risolve questo rifiuto. Il primo tentativo sandbox Operation not permitted è un impedimento locale distinto, superato dal test autorizzato fuori sandbox. Nessun comando remoto eseguito.

Nella sessione server già autenticata occorre leggere quale processo ascolta6543, percorso effettivo AuthorizedKeysFile/AuthorizedKeysCommand, eventuali Match/root restrictions e ragione del rifiuto nei log di autenticazione all'orario del tentativo. Non modificare policy o permessi alla cieca. Comandi iniziali solo lettura, senza chiavi private:

```sh
ss -ltnp 'sport = :6543'
stat -c '%U:%G %a %n' /root /root/.ssh /root/.ssh/authorized_keys
ssh-keygen -lf /root/.ssh/authorized_keys
/usr/sbin/sshd -T | awk '$1 ~ /^(port|pubkeyauthentication|permitrootlogin|authorizedkeysfile|authorizedkeyscommand|strictmodes)$/ {print}'
```

`sshd -T` vale solo per la configurazione predefinita: se il listener usa un altro binario/file configurazione bisogna interrogare quello; i Match richiedono `-C` con indirizzo client effettivo. Consultare i log localmente senza incollare dati personali o altri accessi nel rapporto pubblico. Il successo del blocco di append non dimostra che quel listener legga quel file.

## Blocchi effettivi

- B1: integrazione locale completata e riferimento318 recuperabile; restano review del nuovo manifest e futuro SHA/pubblicazione, non autorizzati ora.
- B2: rifiuto autenticazione; impossibili CLI/FPM/estensioni/MariaDB/segfault attuali e prove su stack destinatario. Serve risolvere l'autorizzazione della chiave sul listener6543 senza indebolire la policy.
- B3: impossibili metadata/schema/migration destinatario; recuperabilità produttiva ancora non attestata. Nessuna migration o acquisizione backup produttivo eseguita.
- B4: patchComposer12 integrata; restano debug/cache/vhost/devserver/artefatto pubblico effettivi. npm53righe classificate non azzerate; gate provider separati prima attivazione.

Accettazione locale positiva conservata, BASELINE IN AUDIT — NON VALIDATA. Nessuna validazione produttiva, commit, push, deploy, modifica server, migration produttiva o trasmissione reale.
