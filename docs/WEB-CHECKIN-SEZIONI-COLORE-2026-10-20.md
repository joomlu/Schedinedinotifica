# Web Check-in: sezioni ospite e colore hotel — 20 ottobre 2026

## Stato

**Collaudo isolato PASS — integrazione e pubblicazione C5 pendenti. BASELINE IN AUDIT invariata.**

Base GitHub riletta: `d957e3e110b70d8ef03a4cecabcd616f64d00138`. Ramo: `codex/web-checkin-sezioni-colore-2026-10-20`.
Obiettivo autorizzato: rendere il modulo ospite una schedina suddivisa in sezioni e rendere evidente la scelta del colore hotel, preservando il circuito esistente.
Rischio MEDIO: cambio della navigazione pubblica e della presentazione dei componenti; backend e schema non modificati.

## Problema osservato e modifiche

La pagina di anteprima indicata dall’utente è stata letta nel browser, senza salvataggi. Il DOM mostra contemporaneamente Schedina, Anagrafica, Residenza/Documento e Contatti sotto il passo «I tuoi dati». Il codice conferma il raggruppamento di quattro pannelli nel gruppo 0.

La vista pubblica proposta mostra un solo pannello alla volta: Soggiorno e ospite, Anagrafica, Residenza e documento, Contatti e consensi, Componenti quando previsti, Tassa di soggiorno solo se disponibile secondo il controllo esistente. Navigazione mediante click, frecce, Home/End e pulsanti avanti/indietro. In caso di errore viene aperta la sezione del primo campo segnalato; il dettaglio del componente interessato viene reso accessibile.

Rimossi il passo Riepilogo e conferma e la navigazione supplementare Ospite 2/Ospite 3. I componenti utilizzano le schede Dettagli/Rimuovi già presenti nel formulario canonico. Conservati il limite delle persone e l’esclusione della riga modello vuota. Salvataggio parziale e conferma completa riutilizzano i pulsanti e i gestori canonici, disponibili al piede di ogni sezione. Le schede nascoste restano nel medesimo form: i campi compilati non vengono eliminati durante la navigazione.

Il selettore del colore era già implementato, con persistenza per struttura, validazione HEX, contrasto e snapshot degli inviti. La modifica lo rende visibile sotto il logo nella pagina Personalizza email. Il salvataggio del colore resta un’azione esplicita prima della personalizzazione del messaggio; occorre rigenerare l’anteprima dopo il cambio. Nessuna estrazione automatica di colori dal logo o modifica del trasporto.

File applicativi: `resources/views/web-checkin/public.blade.php` e `resources/views/web-checkin/comunicazioni.blade.php`.
Test adattati: `tests/Feature/web-checkin-html.playwright.spec.js` e `tests/Feature/web-checkin-circuito.playwright.spec.js`.

## Verifiche iniziali e limiti storici

- Lettura GitHub e anteprima reception corrente: eseguite. Nessun POST di invio, colore o dati ospite.
- Revisione del diff locale, elenco/statistiche e controllo whitespace: eseguiti. `git diff --no-index --check before changes` non emette errori; nel confronto no-index il codice 1 indica differenze.
- `node --check /tmp/web-checkin-sezioni-sintassi.js`: PASS sintattico sullo script estratto, sostituendo le sole espressioni Blade con valori sintetici.
- `node --check changes/tests/Feature/web-checkin-html.playwright.spec.js`: PASS sintattico.
- `node --check changes/tests/Feature/web-checkin-circuito.playwright.spec.js`: PASS sintattico.
- Laravel/PHPUnit/Playwright, build, rendering Blade, prova visuale delle nuove viste e C5: **NON ESEGUITI**. Questa sessione non dispone del checkout operativo Mac/VM ufficiale né di PHP nel proprio ambiente. Non è stato usato un altro database o server per aggirare il runtime.
- Un tentativo di applicare una sostituzione locale con due operazioni sul medesimo percorso è stato rifiutato da apply_patch senza applicarla; sostituzione eseguita successivamente e sintassi ricontrollata.

Le due suite browser conservano i percorsi parziale/ripresa, componenti, conclusione e recupero reception; aggiornano i selettori, verificano un solo pannello visibile, comandi di salvataggio sempre presenti, navigazione da tastiera e selettore colore visibile. Queste aspettative sono predisposte, **non attestazioni PASS funzionali**.

## Tenant e sicurezza

Nessuna modifica a controller, autorizzazioni, route, CSRF, token, scadenze, SMTP, snapshot email o condizioni della tassa. Form pubblico e salvataggio colore usano gli endpoint esistenti; anteprima reception conserva il fieldset disabilitato. Nessuna modifica a dati reali. La revisione statica non sostituisce le prove negative su due tenant, ancora da eseguire nel runtime ufficiale.

## Piano iniziale di collaudo

Eseguire nel runtime ufficiale le due suite browser adattate con le rispettive fixture e le regressioni PHP Web Check-in, verificando tre larghezze 1440/768/390, singolo/famiglia/gruppo, tassa disponibile/riservata, errori in pannelli nascosti, parziale/ripresa e componenti salvati. Controllare colore chiaro/scuro e preparazione nuova anteprima senza invii reali. Solo dopo esito positivo procedere all’integrazione e all’eventuale rilascio autorizzato con il protocollo vigente.

CODICE MODIFICATO: SI. TEST MODIFICATI: SI. DOCUMENTAZIONE MODIFICATA: SI. CONFIGURAZIONE MODIFICATA: NO. SCHEMA/DATI MODIFICATI: NO. TEST FUNZIONALI ESEGUITI: NO (soli controlli sintattici). COMMIT/PUBBLICAZIONE RAMO: SI, tramite API GitHub. DEPLOY: NO. TRASMISSIONI EMAIL/ENTI: NO.

Riferimento UX consultato: documentazione ufficiale Mews, “The online check-in journey in the Guest Portal” e “Customize your kiosk and online check-in with the Check in form”. Usata esclusivamente per confrontare la divisione per sezioni e la ripresa della compilazione; nessuna nuova funzione commerciale o raccolta documenti introdotta.

## Ripresa operativa autorizzata — 20 ottobre 2026

Autorizzati esplicitamente completamento PR #3, integrazione in main e pubblicazione mediante C5, subordinati ai controlli. Checkout originario con lavoro documentale preesistente preservato; revisione in worktree separato sul ramo della PR. VM ufficiale `schedine-sviluppo-interno`, PHP 8.3.33 e MariaDB 11.8.9; daily distinto e preservato. Il primo stato Lima «Broken» nel sandbox dipendeva dai permessi di lettura dell’identità di avvio: verifica fuori sandbox Running e hostname conformi. Nessun test Laravel sul Mac.

R1: identità DB/HTTP, 23 rifiuti override, build e GEO PASS; PHP 265 test / 3.097 asserzioni PASS. Browser NON PASS: dopo rimozione dell’ultimo Componente il pulsante Aggiungi restava nascosto. Corretta soltanto la vista pubblica, riallineando visibilità a limite e dettaglio aperto. Run interrotto ordinatamente dopo diagnosi; finally conferma risorse rimosse.

R2: stessa selezione PHP 265/3.097 PASS sulla vista corretta. Rimozione e nuova aggiunta riuscite; tablet fermo sul selettore esatto Dettagli, che non comprendeva il glifo dell’icona nel nome accessibile. Corretto lo spec per suffisso del comando, senza cambiare il formulario condiviso. Run interrotto ordinatamente; risorse rimosse. Esecuzioni sovrapposte non sommate. Diagnostici conservati in output della task.

Aggiunte verifiche browser per persistenza colore chiaro/scuro, contrasto del servizio esistente, conferma da sezione diversa con errori e recupero di Componenti salvati. Controllo dei messaggi visibili distinto dal select nativo nascosto dal widget GEO. Nessun aumento di timeout o indebolimento di guardie. Corretti quattro fine-file segnalati da `git diff --check`. R3 browser in corso; integrazione e C5 non ancora eseguite. Nessuna email a ospiti reali.

## Esito del collaudo isolato finale

R3: 12 scenari browser PASS. R4: 15 test fiscali / 121 asserzioni PASS e 6 scenari HTML/colore PASS; 3 tassa NON PASS per tipologia generale assente nella nuova fixture B. Completata soltanto la fixture, con attestazione preventiva del profilo fiscale; R5: 3 casi Tassa informativa PASS a 1440/768/390. Copertura finale distinta: 6 circuito R3, 6 HTML R4, 3 Tassa R5; ripetizioni non sommate. Le 17 guardie DB/avvio/proxy sono PASS;23 override rifiutati, build e GEO PASS. Ispezionate direttamente schermate desktop, tablet, errore Componente mobile e Tassa mobile.26 immagini finali e104 sorgenti host/clone sigillati nella [verifica](evidenze/web-checkin-sezioni-colore-2026-10-20/verifica.json). Nessun cambiamento applicativo dopo R2.

Recuperata capacità ritirando sette sole cache dbdata di recuperi C5 storici, dopo hash integrali, dump/ridump identici e assenza di utilizzatori; SQL, cifrati, chiavi, upload, backup e diagnostici preservati. Soglie e dimensione VM invariate. Integrazione in main e C5 esplicitamente autorizzate dall’utente, ancora pendenti al momento di questo sigillo.
