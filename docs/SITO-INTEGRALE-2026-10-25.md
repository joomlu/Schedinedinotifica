# Sito commerciale — completamento integrale del 25 ottobre 2026

**QUALIFICA ISOLATA PASS; pubblicazione in attesa del nuovo C5 e della capacità. BASELINE IN AUDIT invariata.**

L’utente ha richiesto esplicitamente confronto con il sito sostituito, audit del prodotto, implementazione, verifiche e pubblicazione. Scope: sito pubblico Laravel e contatti CRM, senza cambiare logica del gestionale, schema o dati reali. Rischio medio per contatto pubblico, gestione delle risorse e rilascio. Lavoro nel worktree indipendente `sito-integrale-2026-10-25`, base main1a2e5e1f; nessun reset o sovrascrittura dei worktree precedenti.

## Confronto e contenuti

[Matrici e fonti](CONFRONTO-SITO-2026-10-25.md), [funzioni e requisiti](AUDIT-CONTENUTI-SITO-2026-10-25.md). Identificata la versione sostituita a90d63aa dal rilascio effettivo precedente: il suo listino Basic200/ProfessionalPro340/Enterprise su richiesta non viene confuso con altre proposte storiche. Listino finale approvato50/100/200 appartamenti e320complessivi per1/2strutture, annualeIVAesclusa, preventivi oltre soglie eMaxFull personalizzato invariati.

Recuperati o resi espliciti TabellaA, proprietari e multistruttura, modelli e storico WebCheck-in, famiglie/gruppi, importazioni/stampe/recupero. Nuova `/ampliamenti` autonoma, menu/footer/CTA coerenti, vecchia `/moduli#stato-funzionalita` conservata. Nessuna integrazione o accettazione esterna inventata.

## Problemi riscontrati e correzioni

- La vecchia voce ampliamenti indicava una sezione di `/moduli`, non una pagina propria. L’accesso diretto a `/ampliamenti` cadeva sul catch-all autenticato:302versologin. Aggiunta rotta pubblica nominata, pagina dedicata e aliasHTML.
- La conferma contatto si basava sul solo HTTP200. Riprodotti sulla baseline tre falsi successi con JSONsenza ricevuta/okfalse/rispostaHTML. Ora successo soltanto con oktrue e lead_id intero positivo, dati conservati su errore.
- CSS/JS avevano URL invarianti; introdotti nomi con hash del contenuto, verifica sul server e firma comune header/meta delle sei pagine. AssetGET senza stato/cookie, pagine private e protezioni dei moduli preservate. Questa correzione impedisce il riuso di bundle diversi dal documento nuovo; non dimostra che la cache fosse la causa della segnalazione dell’utente.
- Il form senza action/method espliciti poteva usare GET in assenza di JS. OraPOST verso rotta nominata, redirect303 e feedback server soltanto dopo salvataggio; errori e dati ripopolati con escaping e whitelist.
- Interesse selezionato, piano eMaxFull conservati nel riepilogo canonico e nella coppia lead/attivitàCRM. Consenso validato sul server. Richieste identiche entro10minuti ritrovano una coppia già salvata, senza duplicarla; nuove richieste distinte restano ammesse. Scritture pubbliche serializzate con lock, salvataggio transazionale, nessun nuovo schema.

## Prove e stato corrente

Rilettura pubblica prima delle modifiche:28URL e105ancore conformi. Prova in sola lettura della versione online a1440/768/390:cinque pagine, clic menuModuli, tour e tastiera, prezzi/MaxFull e annullamentoPASS; nessun POST/erroreJS/HTTP. Non riprodotti il codice specifico dell’errore utente o una risposta origin con vecchio HTML: segnalazione mantenuta distinta, browser/messaggio esatto non disponibili.

`BASE-CONFERMA` sulla baseline1a2e5e1f:3browserNONPASS per falsa conferma riprodotta, zero scritture reali. `R1` sul primo candidato:190PHP/2039asserzioni,1NONPASS dovuto all’asserzione sul vecchio testo home. Backend nuovo senza altri rilievi in quella prova; browser non avviati dopo il fallimento. Runtimes effimeri rimossi; diagnostici conservati. Questi risultati non vengono sommati né presentati come qualifica finale.

R2 sul commit946028fd:192PHP/1994asserzioni,2NONPASS per middleware di contesto ancora dipendenti dalla sessione nella rotta asset resa senza stato; browser non avviati. Corretta esclusione soltanto per GET delle risorse pubbliche. Revisione indipendente ha inoltre individuato reset dopo errore nativo che ripristinava i valori precedenti: rimossi i default solo dopo conferma valida e aggiunta prova errore→correzione→salvataggio→campi vuoti.

R3:192PHP/2084asserzioni,1NONPASS nel nuovo test: il controllo asset con utente struttura lasciava autenticata la successiva richiesta di pagina senza struttura attiva. Riordinato soltanto il test, conservando tutte le asserzioni e le protezioni applicative; browser non avviati.

R4:192PHP/2091asserzioniPASS,7/16browserPASS. DIAG-CSS ha confermato hash dei byte corretto ma Content-Type text/x-asm e zero regole CSS applicate per i nuovi URL Laravel: corretto MIME esplicito CSS/JS/JSON e aggiunte asserzioni header e cssRules nel browser. Difetto introdotto dal candidato, rilevato prima del rilascio; non attribuito al sito1edf o alla segnalazione originaria.

R5:192PHP/2103asserzioniPASS,14/16browserPASS. CSS corretto e sei percorsi precedenti completamentePASS; i due nuovi percorsi tablet/mobile cliccavano il menu della pagina successiva prima del completamento della navigazione. Aggiunto waitForLoadState(load) nel solo helper, senza modifiche applicative, riduzione di asserzioni o aumento dei timeout.

Qualifica finale [documentata](evidenze/sito-integrale-2026-10-25/verifica.json): **R6 su `77baaa96d59939094cdc7cbd083943359c69490f`:192PHP/2103asserzioni e16browserPASS**, otto suite per sito/CRM, ISTAT, tenancy e barriereQuestura. Sei pagine×1440/768/390, URL/hash/MIME/regoleCSS, ancore, percorso reale, accesso diretto/reload/indietro/avanti, tour e tastiera, errori e persistenzaCRM/backoffice sintetico. Build/GEO,23rifiuti override e4402digestPASS. Tutti i runtime propri rimossi.

R8-IMMAGINI su `e363291d719afb94b39a55467aeee6402191906a`:3percorsiPASS,12figure viste e decodificate,15PNG. Applicazione e tutti i sorgenti R6 byte-identici; aggiunto soltanto il nuovo test di acquisizione. Catture precedenti /ampliamenti con figure bianche escluse dalla consegna finale; integrità dei file e naturalWidth erano conformi, nuova acquisizione dopo decode/scroll/dueframe. Non attribuito un guasto agli asset senza prova. R7-IMMAGINI NONPASS1/3: il test chiedeva decode prima che la richiesta lazy fosse avviata; aggiunto caricamento eager e attesa complete/naturalWidth prima di decode, senza ridurre controlli.

Anteprima nuova [loopback63352](http://127.0.0.1:63352/), contatti esplicitamente disattivati senza registrazione; backend reale qualificato soltanto nel runtime sintetico. Anteprima63351 precedente e checkout originali preservati.

Pubblicazione espressamente autorizzata, ma **C5 nuovo non eseguito**: circa1,97GB liberiVM prima dei test contro il fabbisogno PREPARE+S0+riserva. Piano concreto `/private/tmp/sito-integrale-20261025/ritiro-derivati-verificati.py` verifica tre recuperi chiusi,3084611584byte nominali di soli extracted/dbdata/db-tmp, cifrati/SQL/ridump/file/metadati/quiescenzaPASS; originali e chiavi conservati. La revisione automatica ha respinto il ritiro ricorsivo come root perché i consensi precedenti a custodia/deduplicazione non coprono esplicitamente questa rimozione. Nessun ritiro eseguito; consenso specifico richiesto e pendente. Non ridotti limiti o riserve, nessun ampliamento disco o aggiramento. Dopo capacità conforme: nuova procedura PREPARE/S0/recuperi/custodie, QAonline con tre richieste identificabili e puliziaHMAC66, monitor900s e attestazione SHA. I PASS del precedente sito non provano questa pubblicazione.

## Limiti esterni e dati mancanti

AccettazioneRoss1000/StayTour non attestata dal sito né dai double; credenziali, codici e abilitazioni restano requisiti del relativo servizio. Dati societari/PartitaIVA, informativa privacy, recapiti aggiuntivi e condizioni operativeMaxFull da confermare. Nessun recapito storico viene promosso a dato attuale senza prova. Tour con schermate autentiche disponibile; nessun video inventato.


## Revisione PR25 e nuova qualifica R10

Il bot ha individuato un P2 reale sul commit5646a0ff: timeout del lock pubblico rispondevaJSON anche al form nativo, perdendo i dati. Corretto con303aicontatti, errore visibile e oldinput conservato; JSON503/RetryAfter2 invariato. Nuovo test PHP copre entrambi i circuiti e assenza di scritture. Rimosso inoltre il vecchio feedback nativo soltanto dopo un successivo salvataggio JS confermato; prova browser esplicita.

R9:193PHP/2121asserzioni e19browserPASS sul primo correttivo; superato dalla nuova prova. **R10 sul commit1a6bc6bcb5845023ff03da1d3fa80ca42fbdf02f:193PHP/2121asserzioni e19browserPASS**, otto suitePHP e cinque specbrowser, build/GEO/23override/4403digest conformi. Include12figure viste/decodificate e15PNG. La ricevuta corrente verifica.json sostituisce la precedente, conservata in verifica-R6-R8.json; nessun esito ripetuto sommato. Nuova pubblicazione ancora pendente capacità/consenso specifico/C5. Nessuna DDL, dato reale, email o invio ente.
