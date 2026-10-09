# Finestra backup destinatario: acquisizione abortita — 9 ottobre 2026

Finestra autorizzata attivata il2026-10-09T20:35:23Z. Il wrapper ha invocato Maintenance.close con indexhashverificato, nessunrestart/stop/bloccoDB. Il testHTTPSall'origine217.174.149.23 ha ottenuto il codice503 atteso, ma la successiva asserzione combinata su markerbody e Retry-Aftercase-sensitive ha fallito con `Marker503 mancante`.

Per protocollo fermata acquisizione prima del dump/filearchive: nessunbackup recuperabile acquisito e nessunPASSdi coerenza. Nessuna fase restore/integrità/decifrabilità/settemigration/recovery/stabilità avviata. Creata soltanto directoryriservata di diagnostica sotto /home/tanggosoftware/backups, fuoriURLpubblici,0700/file0600, contenente wrapper/bundle e prove deltentativo, non unbackupDB. Nessunsecret copiato nei documenti/Git.

Sigillo.env/APP_KEYricontrollato; Maintenance.open ha rimosso esclusivamente i due file generati, niente riparazione automatica. **Durata misurata0,031secondi**, controllo riapertura `/login` HTTP200; successiva lettura conferma down/maintenance.php assenti. Finestra sotto il massimo10minuti. Nessunprocessooperativo arrestato, nessuna modifica a vhost/PHP/configurazione.

## Limite del diagnostico

Il wrapper usa un'asserzione unica per marker e header `Retry-After: 60`; suHTTP2 gliheader possono avere diversa capitalizzazione. Questa è una possibile causa diagnostica, non una causa dimostrata. Il controllo riapertura ha riutilizzato i nomi di filebody/header, sovrascrivendo le due risposte503 raw nella directoryriservata: verbale e sorgenteoriginali conservati, ma impossibile distinguere ora markerassente da headercase senza nuovo tentativo. Non attribuire il fallo allaapp o dichiararlo risolto. Registraesplicitamente il limite di conservazione, nessuna falsificazione dello storico.

Per un eventuale nuovo tentativo dello stesso backup: prima correggere e provare infixture il controlloheader case-insensitive, salvare risposte perstato/tentativo con nomiimmutabili e cause separate (codice/marker/header), registrare tempo effettivo dall'attivazione alla rimozione; mantenere STOPe riapertura suqualsiasifallimento. Nonriattivata ora la barriera dopo ilFAIL. Tutte le fasisuccessive restano ferme finché una nuova acquisizionecoerente completa passa ilgate; nessuna migration produttiva o deploy.

## Stato

Accettazione locale delcandidatoLaravel12 e66guardiesupervisor conservata. Candidato aggiornato con il solo esito documentale, nessuna correzione applicativa. B2stabilità eB3recuperabilità aperti; B4artefatto/confine eB1futuroSHA rimangono. BASELINE IN AUDIT — NON VALIDATA, nessuna validazioneproduttiva. Nessuncommit/push/deploy/trasmissione.


## Esito successivo conservato separatamente

Il primoFAIL resta intatto. [Nuovo tentativo eproveeffettive](BACKUP-RESTORE-STABILITA-DESTINATARIO-2026-10-09.md):31,661s,backup/R0/R1/R2PASS,CLIstandardSIGSEGV,FPMfermato. Nessundeploy omigrationproduttiva.
