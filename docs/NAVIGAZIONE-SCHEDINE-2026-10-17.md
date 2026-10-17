# Navigazione Schedine — 17–18 ottobre 2026

**Pubblicato e verificato:** `dbee3028494eca70b99c5b77f06d07c95688689b`. Il consuntivo finale aggiunge soltanto documentazione; non richiede un nuovo deploy applicativo.

## Modifica visiva

Cinque sole viste modificate: voce laterale unificata «Nuova / Elenco / Bozze» sotto Schedine e barra condivisa sopra nuova schedina, elenco, bozze e modifica. Riutilizzate le classi e il pastello reali di Clienti, senza modificare SCSS. La scheda attiva usa `aria-current`; il recupero esistente ha etichetta e nome accessibile «Riprendi compilazione». Conservato l'accesso Arrivi. Nessun contatore nuovo, modulo duplicato o pagina annidata.

Il parziale di presentazione ricorda i soli link con ricerca `q` e pagina, separatamente per elenco, bozze e struttura. Controller, servizi, query, calcoli, validazioni, stati, route, moduli, pulsanti di salvataggio e script esistenti sono invariati. Nessuna migrazione.

## Collaudo isolato

Runtime ufficiale in copia CoW separata, con fixture sintetiche: **39 test PHP / 206 asserzioni PASS**. Coperti creazione e modifica, salvataggio incompleto, ripresa della bozza, riferimenti cliente e dinieghi tra strutture.

**Tre prove browser a 1440, 768 e 390 px PASS:** contenuti, collegamenti diretti, ritorni con ricerca/pagina, tastiera Tab/Invio, scheda attiva, controlli di almeno 44 px entro il viewport e zero scritture al cambio sezione. Confronto degli stili effettivi con Clienti positivo. Build, GEO immutabile e 23 rifiuti di override PASS. Guardie infrastrutturali complete: **17 controlli PASS**.

Diagnostici conservati: primo avvio guardie senza Node nel PATH, poi ripetuto col runtime previsto; timeout della prova tablet nel primo confronto degli stili, seguito da un run completo positivo senza aumentare timeout/retry o modificare l'applicazione. Due BrokenPipe del proxy durante la chiusura del primo run, dopo i PASS. Runtime ripuliti dal launcher. [Prove iniziali](evidenze/navigazione-schedine-2026-10-17/verifica.json) e [guardie complete](evidenze/navigazione-schedine-2026-10-17/guardie-complete.json).

## Capienza e conservazione

Preflight inizialmente insufficiente. In questa task sono stati rimossi soltanto **72.384 derivati vendor/build** di recuperi conclusi, dopo verifica SHA/HMAC e assenza di processi o mount. Altri **9.689 file identici** Git/documenti/riferimenti/risorse sono stati conservati mediante CoW, con hash, metadati e attributi verificati e senza hardlink. DB, SQL, backup, env, chiavi, upload, loghi e diagnostici esclusi; nessuna crescita VM.

Preflight prima di PREPARE: **3.746.631.680 byte disponibili contro 3.709.771.776 richiesti**. Prima del secondo RUN: **2.650.288.128 contro 2.385.014.784**. Margine di 1 GiB e soglie invariati, anche dopo le pulizie. Spazio VM conclusivo: **1599782912 byte**. Inventari e incrementi puntuali in `output/navigazione-schedine-2026-10-17/` e nel deposito privato VM; non equivalgono al saldo complessivo durante i nuovi recuperi.

## Pubblicazione effettiva — 18 ottobre 2026

Nuovo PREPARE indipendente PASS. Primo RUN fermato a 21,515 secondi da «Nuovo ingresso nel pool precedente: STOP», prima di esecuzione e DDL. Il guardiano ha riaperto la baseline; database byte-identico allo snapshot e login HTTP 200. Il controllo conservativo può rilevare altri siti sul socket condiviso: non è stato attenuato e nessuna richiesta altrui è stata interrotta.

Successiva osservazione: 55 campioni quieti su 55. Base PREPARE verificata e invariata; ripresa con nuovo piano/hash, T0, nonce e S0, senza riutilizzare il checkpoint fallito. **Secondo RUN PASS / CANDIDATO_ACCETTATO:** S0 cifrato, trasferito, riletto e recuperato indipendentemente in **241.809 s**; RUN concluso in **320.228 s**, zero DDL. Guardiani di entrambi i tentativi conclusi, inattivi e disabilitati; la prima unità è stata disabilitata esplicitamente nel controllo di chiusura. Budget 1800/600/1379/421 e gate invariati.

## Verifica online e monitor

Con Superadmin, verificate tutte e tre le sezioni a 1440/768/390 px: un solo modulo su Nuova, scheda attiva corretta, controlli alti circa 60 px entro il viewport, pastello, leggibilità, tastiera e ritorni con ricerca/pagina. Elenco e Bozze verificati con filtro a zero risultati; filtri sintetici rimossi al termine. Nessun dato reale creato, modificato o eliminato per il collaudo. Creazione/modifica, recupero della bozza e dinieghi sono provati soltanto in isolamento; nessuna nuova prova online di salvataggio, stampa o invio dichiarata.

**Monitor: 917.672 secondi reali, 31 campioni e 279 sonde HTTP/TLS/CSS PASS.** Zero nuovi errori applicativi o journal FPM registrati dal T0. Il logging PHP resta quello originale: non si garantisce l'assenza di errori non registrati.

Database byte-identico al nuovo S0 anche dopo il browser; 75 persistenti, env/APP_KEY, upload e loghi preservati; 156 migrazioni e 64 tabelle invariati. Conservati il delta FPM upload attivo e il PNG originale di Rimini. [Ricevuta di rilascio](evidenze/navigazione-schedine-2026-10-17/rilascio.json).

Sorgenti locale/GitHub/VM/produzione allineati al candidato prima del consuntivo. La VM conserva documenti pregressi e binding del runtime: non è attestato un Git VM pulito. SHA conclusivi, compreso il commit soltanto documentale, nella ricevuta esterna `output/navigazione-schedine-2026-10-17/ricevuta-finale.json`. Modifiche estranee preservate ed escluse; **BASELINE IN AUDIT generale invariata**.
