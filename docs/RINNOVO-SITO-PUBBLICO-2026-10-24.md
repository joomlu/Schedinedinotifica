# Rinnovo del sito pubblico — 24 ottobre 2026

## Approfondimento completo — candidato qualificato pronto a revisione

Il nuovo candidato `5188ce446d5c67c6cac880514800618b9aeccade` completa contenuti e architettura delle cinque pagine nello stesso Laravel e nello stesso worktree. **Pronto alla revisione dell’utente, non integrato su main e non pubblicato. BASELINE IN AUDIT invariata.** La [ricevuta dell’approfondimento](evidenze/rinnovo-sito-pubblico-2026-10-24/ampliamento/verifica.json) distingue PHP R6 e browser/immagini R7 su codice applicativo byte-identico. Le prove e le immagini R4 restano soltanto storiche, nella sezione seguente.

L’anteprima locale aggiornata dal principale simula il successo del modulo con una conferma esplicitamente dimostrativa: nessun database, email o scrittura. Il backend Laravel è stato invece provato nella sola VM ufficiale, con il CRM effimero del runner. Nessun test concorrente con ISTAT.

Gap risolti nel nuovo candidato: destinatari e problemi della reception, percorso di avvio in tre passi, sezioni autonome Arrivi/ospiti/Tassa/Questura/ISTAT, distinzione richiesta/referente/compilazione/revisione e soggiorno effettivo, novità reali dei percorsi di ottobre, stato delle funzionalità e dei canali esterni. Le cinque URL restano invariate; nuove ancore e collegamenti interni, nessuna nuova route.

| Fonte corrente | Capacità e stato sostenibili | Destinazione pubblica |
| --- | --- | --- |
| Maestro 18 ottobre Arrivi; `SchedinaController`, `ArriviDay` | Preparazione della giornata, distinta da Bozze ed effettiva; conferma e rivalidazione; non prenotazioni future | `/moduli#arrivi`, `/demo#percorso-reception` |
| Maestro 18–22 ottobre Web Check-in; `WebCheckinRichiesta`, `WebCheckinCircuito`, controller e messaggi | Richiesta/referente separati, modelli, anteprima/conferma email, stati, compilazione parziale e revisione | `/moduli#web-checkin`, `/moduli#ospiti`, `/demo#percorso-ospite` |
| Maestro 24 ottobre e approfondimento pendenti; servizi e report Tassa | Calcolo/documenti/storico e rettifica presenti; validità per contesto; accettazione StayTour da verificare | `/moduli#tassa-soggiorno` |
| Maestro Questura/ISTAT; controller e servizi dedicati | File e controlli locali implementati; configurazione, canali e accettazione ufficiale non attestati universalmente | `/moduli#questura`, `/moduli#istat`, `/moduli#stato-funzionalita` |
| `PresenzeController`, `CalendarioController`, `NotificheController`, gestione operativa e Help Center | Report, calendario, utenti/consegne, notifiche e ticket presenti; nessuna promessa di vendita camere/pagamenti | `/moduli#organizzazione` |
| `resources/views/layouts/footer.blade.php`, marchio HTML e dominio effettivo | Schedine di Notifica — Tanggo Platform2.0; il principale verifica radice e sottodominio sulla stessa installazione | `/#piattaforma` |
| Informazioni del percorso e configurazione struttura nel progetto | Destinatari e avvio da personalizzare per Comune, ruolo e attività | `/#per-chi`, `/#come-iniziare`, `/demo#prima-di-partire` |

Il riferimento utente «Tango.software» con una g non viene equiparato a Tanggo. Fonti del progetto documentano la doppia g e l’ambiente Tanggo Platform. L’agente principale ha verificato che radice `tanggo.software` e sottodominio applicativo condividono DocumentRoot: nessun nuovo progetto esterno, DNS o vhost modificato. L’utente ha confermato esplicitamente `schedinedinotifica.tanggo.software` come sito pubblico del sistema. Canonical e `og:url` dei cinque percorsi utilizzano già questo dominio, coerente con l’APP_URL documentato: nessuna modifica al codice qualificato necessaria dopo la conferma. Metatitoli, descrizioni uniche e Open Graph sono coerenti; nessun DNS, vhost o redirect di dominio modificato.

Non è stata individuata una roadmap approvata con nuove funzioni e scadenze: lo stato pubblico distingue **Disponibile nel software / In verifica / Da definire all’avvio**, senza inventare annunci «in sviluppo». I riferimenti storici dell’Help Center non superano i consuntivi correnti del Maestro.

## Verifiche del nuovo approfondimento

**PHP R6: 35 test / 330 asserzioni PASS**: otto casi dedicati al sito e le regressioni Tassa/GEO/autorizzazioni concordate. **Browser R7: sei percorsi PASS**, cinque pagine a 1440/768/390, con 19 immagini nuove: 15 pagine intere, tre conferme e una vista desktop della sezione Novità. La durata browser è riportata nella ricevuta. Non è dichiarata una nuova esecuzione PHP dentro R7.

Verificati titoli e descrizioni distinti, canonical/Open Graph coerenti, ancore tra le pagine, sezioni autonome e stati delle funzioni, navigazione mobile/tastiera, FAQ, accessi esistenti, parità CSS/JS via SHA256 della risposta HTTP, immagini caricate e assenza di overflow/errori JS/asset. Il modulo mantiene obbligatori, errori, calendario, richiesta CRM sintetica e conferma/focus/Escape. Ventitré override rifiutati, build asset/GEO e 4.383 sorgenti attestati dal runtime ufficiale. Nessun cambiamento al backend del modulo o all’autenticazione.

R5 NONPASS conservato: tre navigazioni usavano un nome accessibile obsoleto con la freccia decorativa; gli altri tre percorsi form e PHP erano verdi. Corretto solo il selettore, mantenendo tutti i controlli e i timeout. R6 è funzionalmente PASS, ma l’ispezione ha escluso tre immagini Prezzi: la cattura fullPage dopo il clic FAQ componeva header sticky e skip-link nella posizione di scroll intermedia. R7 acquisisce da scrollY0, controlla header a 0 e stabilità delle dimensioni tra due frame; nessuna modifica CSS del sito, attesa fissa, force o asserzione rimossa. I tre PNG R6 non sono consegnati come evidenza visiva corrente.

Ispezionate le 19 immagini finali R7: nessun taglio o sovrapposizione rilevato nelle schermate consegnate. Il nuovo collettore conserva output distinti per etichetta, senza residui cumulativi dei tentativi precedenti. Originali R5/R6 e R4 preservati; nessun log integrale pubblicato. Esecuzioni sovrapposte non sommate.

Processi e directory effimeri conclusi e rimossi dal runner. Riserva VM di 1 GiB rispettata nei campioni dell’approfondimento; daily `945ef553` e SELinuxEnforcing preservati. Soli osservatori propri conclusi. VM lasciata accesa e libera, consegnata al coordinamento principale/ISTAT. Nessun dato condiviso, ospite reale, account reale, email o invio a enti coinvolto.

## Anteprima e dati da definire

Consegna visiva corrente: [novità desktop](evidenze/rinnovo-sito-pubblico-2026-10-24/ampliamento/novita-desktop.png), [home mobile](evidenze/rinnovo-sito-pubblico-2026-10-24/ampliamento/home-mobile.png), [moduli tablet](evidenze/rinnovo-sito-pubblico-2026-10-24/ampliamento/moduli-tablet.png), [conferma mobile](evidenze/rinnovo-sito-pubblico-2026-10-24/ampliamento/conferma-mobile.png). PNG originali R7 copiati byte per byte, con digest nella ricevuta.

Prima della pubblicazione: revisione dell’utente, dati societari/Partita IVA/privacy, recapiti e condizioni commerciali. I limiti delle integrazioni esterne restano quelli del contesto applicativo, senza certificazioni universali o roadmap inventate. Il consuntivo successivo modifica soltanto documentazione/evidenze, non il codice qualificato.

## Prima consegna — qualifica R4 precedente, conservata come storico

Questa sezione e le immagini della cartella padre descrivono il predecessore, non la UI corrente approfondita.

Stato: candidato qualificato `459ca0d72e0bf82a22cfe32da007d376bdc62eb2`, pronto per la revisione nel worktree indipendente. Nessuna integrazione o pubblicazione. **BASELINE IN AUDIT** invariata. Questo documento descrive il perimetro del rinnovo; il [Maestro](maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md) resta la fonte maestra.

### Separazione del lavoro

Worktree web indipendente gestito dall’app, ramo `codex/rinnovo-sito-pubblico-2026-10-24`, base `455a792cfc163d9cd52ff3964bc9629222ac5810`.

Il secondo agente modifica `web/**`, i due nuovi test dedicati e questo rapporto. Durante la qualifica è emersa la dipendenza dalle copie statiche `public/site-assets/styles.css` e `public/site-assets/app.js`: coordinata e approvata dall’agente principale, che le ha sincronizzate con i sorgenti web. I due file sono ora coperti anche da guardie di parità. Agente principale proprietario di Maestro, coordinamento, ambiente isolato e verifiche del software corrente. Nessuna modifica a route, controller, autenticazione, servizi interni, migrazioni o configurazione. Il checkout di lavoro precedente e le sue modifiche restano separati e preservati. Nessuna modifica al database condiviso.

### Inventario e contenuti studiati

Il sito esistente è composto da HTML statici in `web/`, serviti da `PublicSiteController` attraverso cinque route pubbliche; `site-assets` ha una route Laravel con controllo del percorso, ma i file statici presenti in `public/site-assets` vengono serviti prima del controller: la parità delle copie CSS/JS è quindi necessaria. `web/accesso.html` è un residuo statico: le route `/accesso` e `/accesso.html` reindirizzano già a `/login`.

Sono stati letti `AGENTS.md`, Maestro, `docs/TESTING_ISOLATION.md`, controller e route del sito, `WebsiteContactController`, controller di accesso/registrazione, Help Center e i servizi delle funzionalità descritte. I correttivi e i limiti della Tassa risultano dal consuntivo corrente del Maestro. Fonti applicative delle capacità pubblicizzate:

| Area | Riscontro nel progetto | Presentazione pubblica |
| --- | --- | --- |
| Clienti e schedine | `CustomerController`, `SchedinaController`, Arrivi, storico/cestino e Help Center | Recupero dati, bozze e verifica prima della registrazione |
| Web check-in | `WebCheckinController`, `WebCheckinMessaggio`, consuntivi del Maestro | Link ospite, sezioni, logo/colori, anteprima comunicazione; controllo finale della reception |
| Questura | Controller, servizi di trasporto e ricevute, Help Center | File TXT, controlli, ricevute disponibili e storico |
| ISTAT | `IstatTabellaAController`, servizi di trasmissione/esito | Tabella A Emilia-Romagna / Ross1000, preparazione e gestione dei flussi; nessuna promessa di compatibilità nazionale o esito ufficiale automatico |
| Tassa | `TassaDiSoggiornoService`, report e consuntivo 24 ottobre | Calcolo/documenti subordinati a configurazione e contesto; nessuna conformità universale dichiarata |
| Organizzazione | Calendario, presenze, notifiche e supporto | Strumenti operativi già presenti, senza nuove integrazioni promesse |
| Contatto commerciale | `WebsiteContactController` | POST `/contatti/richiesta`, lead e attività CRM; nessuna promessa di email inviata dal modulo |

### Intervento

- Cinque pagine riscritte: panoramica, funzionalità, percorso operativo, prezzi/informazioni e contatti. Rimossi testi destinati al venditore, ripetizioni, poster di video inesistenti e affermazioni di risultato non dimostrate.
- Palette vino/teal e marchio esistente conservati; gerarchia tipografica, spaziatura e componenti coerenti. Font di sistema, senza richieste Google Fonts o nuovi servizi esterni.
- Schema del percorso nella home dichiarato illustrativo: HTML/CSS, nessuna schermata operativa o dato di ospiti. Le illustrazioni locali esistenti sono riutilizzate. Nessun asset storico cancellato o alterato.
- Navigazione responsive, pagina corrente, collegamento per saltare al contenuto e chiusura menu con Escape/ritorno focus. Larghezze di riferimento 1440/768/390.
- Modulo contatti mantiene campi e payload esistenti, selezioni e calendario locali. Etichette corrette, assenza di etichette annidate, messaggi di errore, blocco doppio invio, conferma con focus e contenimento della tabulazione, Escape e ritorno al pulsante.
- Accesso resta `/login`. Attivazione rimanda al modulo; nessuna promessa di auto-registrazione. Route e form di registrazione applicativi non modificati.

### URL, prezzi e dati da confermare

Nessuna URL pubblica modificata: `/`, `/moduli`, `/demo`, `/prezzi`, `/contatti`. Alias `.html` e accesso restano gestiti dai reindirizzamenti esistenti. Non occorrono nuovi redirect. Collegamenti interni relativi alla stessa origine; unico collegamento esterno nel footer è quello già esistente all’autore.

Dominio corrente verificato dall’agente principale in sola lettura: `https://schedinedinotifica.tanggo.software/`, home e login HTTP 200 con verifica TLS positiva. Collegamento autore `https://www.cv.joomlu.com/` verificato con GET autorizzato: HTTP 200, TLS verify 0 e URL finale identica. Un primo tentativo nel sandbox senza rete non è usato come prova DNS. Dominio storico `www.tangoschedine.com` non risolve DNS durante il controllo autorizzato dell’agente principale: eliminato dal sito rinnovato e conservato qui come pendente di gestione del dominio.

| Dato | Evidenza e decisione |
| --- | --- |
| Prezzi €200/€340 e piani storici | Presenti soltanto nel vecchio HTML, con nomi incoerenti; manca listino vigente approvato e dettaglio imposte/attivazione. Non riproposti. Pagina prezzi orientata alla richiesta di proposta, senza importi inventati. |
| Email/telefono/indirizzo | `info@tanggo.org`, `05411796994`, Via Roma35 Bellaria presenti negli HTML storici; validità attuale non attestata. Non rilanciati come recapiti confermati. Il modulo applicativo è il canale di contatto verificabile. |
| WhatsApp/fax | Soltanto riferimenti storici nel sito. Non riproposti. |
| Identità | Schedine di Notifica / Tanggo Platform2.0, marchio grafico, Tanggo Experience ed ExperienceS.r.l. preservati dal sito esistente; ragione sociale e dati legali da convalidare prima della pubblicazione. |
| Partita IVA e privacy | Partita IVA mancante nel sito originario; nessuna informativa privacy vigente individuata tra le pagine pubbliche. Rimossi placeholder, senza inventare numeri o testi legali. Occorre acquisire dati aziendali e informativa sul trattamento del modulo prima della pubblicazione. |
| Registrazione | GET/HEAD `/register` online403 osservato dall’agente principale, senza attribuzione applicativa provata. Nel sorgente `Auth::routes()` e controller registrazione restano presenti. Il test isolato verifica il comportamento del codice separatamente dal presidio online; nessun link marketing conduce al403. |
| Tassa e portali | Compatibilità per Comune/struttura/periodo da verificare; il sito non promette conformità fiscale universale, incassi/riversamenti o accettazione automatica del CSV. Pendenti esterni del Maestro invariati. |

La conferma obbligatoria del contatto commerciale già esistente è conservata. Non viene presentata come sostituto di un’informativa privacy. Assenza di policy/retention documentate e protezioni antispam ulteriori resta un tema esistente da coordinare separatamente: nessuna modifica nascosta al backend del form.

### Verifiche

Analisi statica svolta: `git diff --check`, controllo sintattico Node dei due JavaScript, HTML senza ID duplicati né etichette annidate. Nessuna esecuzione Laravel, PHPUnit o Playwright sul Mac.

Selezione dedicata eseguita:

- `tests/Feature/PublicSiteRenewalTest.php`: cinque casi su pagine, alias/accessi, risorse/percorso, richiesta CRM sintetica e validazioni/honeypot senza scritture.
- `tests/Feature/public-site-renewal.playwright.spec.js`: sei percorsi a 1440/768/390, ciascuno con cinque pagine oppure modulo contatti; collegamenti/ancore, menu da tastiera, accesso e registrazione, nessun overflow, immagini caricate, validazioni, errore simulato e invio reale soltanto verso il CRM effimero, conferma accessibile e schermate.

**R4 conclusivo PASS sul candidato `459ca0d72e0bf82a`.** Lo SHA completo è riportato nella [ricevuta aggregata](evidenze/rinnovo-sito-pubblico-2026-10-24/verifica.json). Nella sola VM ufficiale: **32 test PHP / 252 asserzioni**, includendo i cinque dedicati al sito e le regressioni Tassa/GEO/autorizzazioni, e **sei percorsi browser** in 32,5 secondi. Le cinque pagine sono percorse a tutte e tre le larghezze: 15 combinazioni, nessun overflow, errore JavaScript o asset mancante. Menu da tastiera, ancore, accessi, form obbligatori, errore simulato, richiesta CRM sintetica, conferma/focus/ritorno con Escape PASS. Risorse CSS/JS effettivamente ricevute via HTTP confrontate con SHA256 canonico, parità delle copie statiche PASS. Ventitré rifiuti di override, build asset/GEO e 4.383 sorgenti attestati PASS.

R1/R2 NONPASS conservati: nuove pagine con vecchie copie statiche CSS/JS servite al browser; il sintomo di 10 px e il focus assente non sono stati attribuiti al nuovo CSS. Dopo il riallineamento, R3 conferma assenza di overflow e parità degli asset, ma incontra due difetti dello spec: selettore del menu vincolato al nome che cambia da Apri a Chiudi e calendario aperto che intercetta il checkbox. Corretto solo lo spec con selettore stabile e ulteriori asserzioni sui nomi, più normale clic esterno e verifica del calendario chiuso. Nessun force, timeout ridotto, asserzione soppressa o CSS di occultamento globale. R4 ripete integralmente la selezione concordata. Le esecuzioni sovrapposte non sono sommate.

Ispezionate tutte le **18 immagini finali**, cioè 15 pagine e tre conferme: nessun taglio o sovrapposizione rilevato. La raccolta tecnica cumulativa conserva anche vecchi `diagnostico-overflow` ed `error-context`: sono esclusi dalle immagini R4 e dalla consegna, senza cancellarne gli originali. La ricevuta identifica soltanto i file `sito-*.png` generati nuovamente dal test verde.

Il runtime R4 ha fermato e rimosso processi e directory effimeri. Nessun dato condiviso coinvolto; gli invii del form hanno scritto soltanto nel database effimero, poi rimosso dal runner. VM lasciata accesa sotto coordinamento principale per il passaggio di gestione al fronte ISTAT, senza test concorrenti. Riserva VM di 1 GiB rispettata nei campioni fino a fine R4. La sola anteprima statica resta distinta dalla prova backend isolata.

### Consegna e limiti

Anteprima statica locale coordinata dall’agente principale: limitata a loopback, banner di revisione e POST rifiutato senza registrare dati. Schermate definitive prodotte e ispezionate nel runtime ufficiale: [home desktop](evidenze/rinnovo-sito-pubblico-2026-10-24/home-desktop.png), [home mobile](evidenze/rinnovo-sito-pubblico-2026-10-24/home-mobile.png), [contatti tablet](evidenze/rinnovo-sito-pubblico-2026-10-24/contatti-tablet.png), [conferma mobile](evidenze/rinnovo-sito-pubblico-2026-10-24/conferma-mobile.png). Nessun invio email, trasmissione a enti, creazione di account o scrittura su dati operativi.

Prima della pubblicazione coordinata restano da confermare dati legali/privacy, recapiti e condizioni commerciali. Integrazione e pubblicazione non eseguite; il candidato deve essere revisionato con le evidenze del collaudo.

Il commit di questo consuntivo e delle quattro immagini è successivo alla qualifica e soltanto documentale; non cambia i sorgenti applicativi qualificati. Le fonti commerciali mancanti restano pendenti, senza impedire la revisione locale del candidato.
