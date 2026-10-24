# Rinnovo del sito pubblico — 24 ottobre 2026

Stato: candidato qualificato `459ca0d72e0bf82a22cfe32da007d376bdc62eb2`, pronto per la revisione nel worktree indipendente. Nessuna integrazione o pubblicazione. **BASELINE IN AUDIT** invariata. Questo documento descrive il perimetro del rinnovo; il [Maestro](maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md) resta la fonte maestra.

## Separazione del lavoro

Worktree web indipendente gestito dall’app, ramo `codex/rinnovo-sito-pubblico-2026-10-24`, base `455a792cfc163d9cd52ff3964bc9629222ac5810`.

Il secondo agente modifica `web/**`, i due nuovi test dedicati e questo rapporto. Durante la qualifica è emersa la dipendenza dalle copie statiche `public/site-assets/styles.css` e `public/site-assets/app.js`: coordinata e approvata dall’agente principale, che le ha sincronizzate con i sorgenti web. I due file sono ora coperti anche da guardie di parità. Agente principale proprietario di Maestro, coordinamento, ambiente isolato e verifiche del software corrente. Nessuna modifica a route, controller, autenticazione, servizi interni, migrazioni o configurazione. Il checkout di lavoro precedente e le sue modifiche restano separati e preservati. Nessuna modifica al database condiviso.

## Inventario e contenuti studiati

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

## Intervento

- Cinque pagine riscritte: panoramica, funzionalità, percorso operativo, prezzi/informazioni e contatti. Rimossi testi destinati al venditore, ripetizioni, poster di video inesistenti e affermazioni di risultato non dimostrate.
- Palette vino/teal e marchio esistente conservati; gerarchia tipografica, spaziatura e componenti coerenti. Font di sistema, senza richieste Google Fonts o nuovi servizi esterni.
- Schema del percorso nella home dichiarato illustrativo: HTML/CSS, nessuna schermata operativa o dato di ospiti. Le illustrazioni locali esistenti sono riutilizzate. Nessun asset storico cancellato o alterato.
- Navigazione responsive, pagina corrente, collegamento per saltare al contenuto e chiusura menu con Escape/ritorno focus. Larghezze di riferimento 1440/768/390.
- Modulo contatti mantiene campi e payload esistenti, selezioni e calendario locali. Etichette corrette, assenza di etichette annidate, messaggi di errore, blocco doppio invio, conferma con focus e contenimento della tabulazione, Escape e ritorno al pulsante.
- Accesso resta `/login`. Attivazione rimanda al modulo; nessuna promessa di auto-registrazione. Route e form di registrazione applicativi non modificati.

## URL, prezzi e dati da confermare

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

## Verifiche

Analisi statica svolta: `git diff --check`, controllo sintattico Node dei due JavaScript, HTML senza ID duplicati né etichette annidate. Nessuna esecuzione Laravel, PHPUnit o Playwright sul Mac.

Selezione dedicata eseguita:

- `tests/Feature/PublicSiteRenewalTest.php`: cinque casi su pagine, alias/accessi, risorse/percorso, richiesta CRM sintetica e validazioni/honeypot senza scritture.
- `tests/Feature/public-site-renewal.playwright.spec.js`: sei percorsi a 1440/768/390, ciascuno con cinque pagine oppure modulo contatti; collegamenti/ancore, menu da tastiera, accesso e registrazione, nessun overflow, immagini caricate, validazioni, errore simulato e invio reale soltanto verso il CRM effimero, conferma accessibile e schermate.

**R4 conclusivo PASS sul candidato `459ca0d72e0bf82a`.** Lo SHA completo è riportato nella [ricevuta aggregata](evidenze/rinnovo-sito-pubblico-2026-10-24/verifica.json). Nella sola VM ufficiale: **32 test PHP / 252 asserzioni**, includendo i cinque dedicati al sito e le regressioni Tassa/GEO/autorizzazioni, e **sei percorsi browser** in 32,5 secondi. Le cinque pagine sono percorse a tutte e tre le larghezze: 15 combinazioni, nessun overflow, errore JavaScript o asset mancante. Menu da tastiera, ancore, accessi, form obbligatori, errore simulato, richiesta CRM sintetica, conferma/focus/ritorno con Escape PASS. Risorse CSS/JS effettivamente ricevute via HTTP confrontate con SHA256 canonico, parità delle copie statiche PASS. Ventitré rifiuti di override, build asset/GEO e 4.383 sorgenti attestati PASS.

R1/R2 NONPASS conservati: nuove pagine con vecchie copie statiche CSS/JS servite al browser; il sintomo di 10 px e il focus assente non sono stati attribuiti al nuovo CSS. Dopo il riallineamento, R3 conferma assenza di overflow e parità degli asset, ma incontra due difetti dello spec: selettore del menu vincolato al nome che cambia da Apri a Chiudi e calendario aperto che intercetta il checkbox. Corretto solo lo spec con selettore stabile e ulteriori asserzioni sui nomi, più normale clic esterno e verifica del calendario chiuso. Nessun force, timeout ridotto, asserzione soppressa o CSS di occultamento globale. R4 ripete integralmente la selezione concordata. Le esecuzioni sovrapposte non sono sommate.

Ispezionate tutte le **18 immagini finali**, cioè 15 pagine e tre conferme: nessun taglio o sovrapposizione rilevato. La raccolta tecnica cumulativa conserva anche vecchi `diagnostico-overflow` ed `error-context`: sono esclusi dalle immagini R4 e dalla consegna, senza cancellarne gli originali. La ricevuta identifica soltanto i file `sito-*.png` generati nuovamente dal test verde.

Il runtime R4 ha fermato e rimosso processi e directory effimeri. Nessun dato condiviso coinvolto; gli invii del form hanno scritto soltanto nel database effimero, poi rimosso dal runner. VM lasciata accesa sotto coordinamento principale per il passaggio di gestione al fronte ISTAT, senza test concorrenti. Riserva VM di 1 GiB rispettata nei campioni fino a fine R4. La sola anteprima statica resta distinta dalla prova backend isolata.

## Consegna e limiti

Anteprima statica locale coordinata dall’agente principale: limitata a loopback, banner di revisione e POST rifiutato senza registrare dati. Schermate definitive prodotte e ispezionate nel runtime ufficiale: [home desktop](evidenze/rinnovo-sito-pubblico-2026-10-24/home-desktop.png), [home mobile](evidenze/rinnovo-sito-pubblico-2026-10-24/home-mobile.png), [contatti tablet](evidenze/rinnovo-sito-pubblico-2026-10-24/contatti-tablet.png), [conferma mobile](evidenze/rinnovo-sito-pubblico-2026-10-24/conferma-mobile.png). Nessun invio email, trasmissione a enti, creazione di account o scrittura su dati operativi.

Prima della pubblicazione coordinata restano da confermare dati legali/privacy, recapiti e condizioni commerciali. Integrazione e pubblicazione non eseguite; il candidato deve essere revisionato con le evidenze del collaudo.

Il commit di questo consuntivo e delle quattro immagini è successivo alla qualifica e soltanto documentale; non cambia i sorgenti applicativi qualificati. Le fonti commerciali mancanti restano pendenti, senza impedire la revisione locale del candidato.
