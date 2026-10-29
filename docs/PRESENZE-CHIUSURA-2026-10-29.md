# Presenze — chiusura tecnica dei report, 29 ottobre 2026

Il correttivo della PR48 è pubblicato e verificato: il periodo selezionato resta coerente nei filtri, nell’URL e nei report dopo il cambio scheda e la ricarica. Commit applicativo `db4498ea59b656b8a730a9b92baca4368e5b1085`; merge `f1d97f46ed237da6fca107c243dee3401e07d819`, con albero identico.

La chiusura riguarda le cinque schede esistenti — Riepilogo, Dettaglio, Situazione giornaliera, Arrivi/Partenze e Occupazione — e le due stampe. Verificati online anche il passaggio di periodo fra schede, la ricarica e febbraio bisestile.

La qualifica isolata R12 aveva superato 45 test PHP, 213 asserzioni, tre scenari browser a 1440/768/390, build/GEO e 23 controlli negativi degli override. I sorgenti del candidato sono stati confrontati con quella qualifica senza differenze. Nessuna nuova esecuzione della suite globale è dichiarata.

Rilascio C5 positivo, senza migrazioni. Monitor finale: 31 campioni in almeno 900 secondi, 279 controlli HTTP/TLS/CSS positivi e nessun nuovo errore applicativo o FPM. Dopo le verifiche il database risulta byte-identico allo snapshot precedente al rilascio; dati operativi, chiavi e provider preservati.

Restano i limiti già dichiarati del modello: capacità storica incompleta, dati mancanti segnalati, assenza di CSV del modulo e nessuna attestazione di accettazione di specifici modelli esterni. Presenze resta un modulo di report e stampe, senza invii agli enti. **BASELINE IN AUDIT generale invariata.**

Il consuntivo dettagliato, gli inventari, i diagnostici e le schermate sono conservati localmente. Questa sintesi pubblica contiene soltanto informazioni tecniche del rilascio e nessun dato delle strutture o metadato interno di recupero.
