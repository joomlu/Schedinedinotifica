# Web Check-in: sezioni ospite e colore hotel — 20 ottobre 2026

## Stato

**Applicativo `1be29e8a55dea841c9c486ea1c5b44d14c249ba1` online; PR #3 integrata in main, C5, QA sintetica, pulizia e monitor finali PASS. BASELINE IN AUDIT invariata.** [Ricevuta](evidenze/web-checkin-sezioni-colore-2026-10-20/rilascio.json), [QA online](evidenze/web-checkin-sezioni-colore-2026-10-20/qa-online.json).

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

Nessuna modifica a controller, autorizzazioni, route, CSRF, token, scadenze, SMTP, snapshot email o condizioni della tassa. Form pubblico e salvataggio colore usano gli endpoint esistenti; anteprima reception conserva il fieldset disabilitato. Nessuna modifica a dati reali. Nella proposta iniziale le prove negative su due tenant erano pendenti; gli esiti successivi sono nel consuntivo seguente.

## Piano iniziale di collaudo

Eseguire nel runtime ufficiale le due suite browser adattate con le rispettive fixture e le regressioni PHP Web Check-in, verificando tre larghezze 1440/768/390, singolo/famiglia/gruppo, tassa disponibile/riservata, errori in pannelli nascosti, parziale/ripresa e componenti salvati. Controllare colore chiaro/scuro e preparazione nuova anteprima senza invii reali. Solo dopo esito positivo procedere all’integrazione e all’eventuale rilascio autorizzato con il protocollo vigente.

CODICE MODIFICATO: SI. TEST MODIFICATI: SI. DOCUMENTAZIONE MODIFICATA: SI. CONFIGURAZIONE MODIFICATA: NO. SCHEMA/DATI MODIFICATI: NO. TEST FUNZIONALI ESEGUITI: NO (soli controlli sintattici). COMMIT/PUBBLICAZIONE RAMO: SI, tramite API GitHub. DEPLOY: NO. TRASMISSIONI EMAIL/ENTI: NO.

Riferimento UX consultato: documentazione ufficiale Mews, “The online check-in journey in the Guest Portal” e “Customize your kiosk and online check-in with the Check in form”. Usata esclusivamente per confrontare la divisione per sezioni e la ripresa della compilazione; nessuna nuova funzione commerciale o raccolta documenti introdotta.

## Ripresa operativa autorizzata — 20 ottobre 2026

Autorizzati esplicitamente completamento PR #3, integrazione in main e pubblicazione mediante C5, subordinati ai controlli. Checkout originario con lavoro documentale preesistente preservato; revisione in worktree separato sul ramo della PR. VM ufficiale `schedine-sviluppo-interno`, PHP 8.3.33 e MariaDB 11.8.9; daily distinto e preservato. Il primo stato Lima «Broken» nel sandbox dipendeva dai permessi di lettura dell’identità di avvio: verifica fuori sandbox Running e hostname conformi. Nessun test Laravel sul Mac.

R1: identità DB/HTTP, 23 rifiuti override, build e GEO PASS; PHP 265 test / 3.097 asserzioni PASS. Browser NON PASS: dopo rimozione dell’ultimo Componente il pulsante Aggiungi restava nascosto. Corretta soltanto la vista pubblica, riallineando visibilità a limite e dettaglio aperto. Run interrotto ordinatamente dopo diagnosi; finally conferma risorse rimosse.

R2: stessa selezione PHP 265/3.097 PASS sulla vista corretta. Rimozione e nuova aggiunta riuscite; tablet fermo sul selettore esatto Dettagli, che non comprendeva il glifo dell’icona nel nome accessibile. Corretto lo spec per suffisso del comando, senza cambiare il formulario condiviso. Run interrotto ordinatamente; risorse rimosse. Esecuzioni sovrapposte non sommate. Diagnostici conservati in output della task.

Aggiunte verifiche browser per persistenza colore chiaro/scuro, contrasto del servizio esistente, conferma da sezione diversa con errori e recupero di Componenti salvati. Controllo dei messaggi visibili distinto dal select nativo nascosto dal widget GEO. Nessun aumento di timeout o indebolimento di guardie. Corretti quattro fine-file segnalati da `git diff --check`. A quel punto R3 era in corso e integrazione/C5 non erano ancora eseguite. Nessuna email a ospiti reali.

## Esito del collaudo isolato finale

R3: 12 scenari browser PASS. R4: 15 test fiscali / 121 asserzioni PASS e 6 scenari HTML/colore PASS; 3 tassa NON PASS per tipologia generale assente nella nuova fixture B. Completata soltanto la fixture, con attestazione preventiva del profilo fiscale; R5: 3 casi Tassa informativa PASS a 1440/768/390. Copertura finale distinta: 6 circuito R3, 6 HTML R4, 3 Tassa R5; ripetizioni non sommate. Le 17 guardie DB/avvio/proxy sono PASS; 23 override rifiutati, build e GEO PASS. Ispezionate direttamente schermate desktop, tablet, errore Componente mobile e Tassa mobile. 26 immagini finali e 104 sorgenti host/clone sigillati nella [verifica](evidenze/web-checkin-sezioni-colore-2026-10-20/verifica.json). Nessun cambiamento applicativo dopo R2.

Recuperata capacità ritirando sette sole cache dbdata di recuperi C5 storici, dopo hash integrali, dump/ridump identici e assenza di utilizzatori; SQL, cifrati, chiavi, upload, backup e diagnostici preservati. Soglie e dimensione VM invariate. Integrazione in main e C5 esplicitamente autorizzate dall’utente, ancora pendenti al momento di questo sigillo.

## Integrazione e pubblicazione effettive

La PR #3 è stata integrata in main il 20 ottobre 2026, SHA `1be29e8a55dea841c9c486ea1c5b44d14c249ba1`, dopo il correttivo qualificato `af25b6e4f857fafa2acdac0b6745fda3a4f93b73`. 104 sorgenti della task coincidono fra commit, copia VM e produzione; 163 sorgenti/protetti conformi. Nessuna DDL o modifica backend/configurazione/trasporto. I [comandi delle selezioni](evidenze/web-checkin-sezioni-colore-2026-10-20/comandi-collaudo.txt) restano distinti dalle accettazioni operative online.

Primo RUN NON PASS: Mac in Idle Sleep alle 11:31:28 italiane; il ricevente si è sospeso e la ricevuta è arrivata dopo la chiusura del servizio. Guardiano a T0+575.448 s: BASELINE_NON_MODIFICATA, nessun execute/DDL, riapertura e salute/sonde PASS. Il recupero terminato fuori finestra non è stato promosso a S0 accettato; picco VM durante la sospensione non attestato. Cifrati, SQL, diagnostici e custodia Extra del tentativo conservati. Attivata un’asserzione temporanea caffeinate, senza impostazioni permanenti; secondo PREPARE, T0, S0, ricevuta e 55 campioni quiete tutti nuovi. Nessun aumento dei limiti.

Il gate iniziale di capacità ha fermato RUN prima della manutenzione. Ritirate in tutto otto sole cache dbdata ricostruibili (sette storiche e quella del RUN non accettato), dopo dump/ridump identici e inutilizzo. Blocchi identici di sole copie temporanee condivisi CoW con sostituzioni atomiche: byte, UID/GID, permessi, mtime e xattr dei file verificati identici; 6291 mtime di directory riallineati agli inventari. Env, storage, immagini/upload, SQL, cifrati e chiavi esclusi. Conteggi delle ripetizioni CoW non sommati; ricevute e misure separate conservate. Nessuna crescita VM.

Secondo RUN C5 PASS: S0 138.154 s, RUN 236.433 s; 159 migrazioni / 66 tabelle, nessuna DDL. DB byte-identico a S0 prima QA, 75 persistenti/env/APP_KEY/upload/loghi preservati, nove sonde PASS. Nuovi PREPARE/S0 finali su Extra autenticati e decifrati integralmente con chiave separata; verifica integrale PASS anche per tutti i 12 cifrati di entrambi i tentativi. Guardiano finale disarmato, concluso, inattivo e disabilitato; anche quello precedente inattivo/disabilitato. Riserva VM campionata ogni secondo: minimo 1243488256 byte su 371 campioni, soglia 1 GiB invariata.

QA online PASS: sei percorsi circuito e sei HTML/colore a 1440/768/390, clienti/Bozze/ripresa, due tenant e 24 navigazioni responsive, accessi scaduti/revocati/rigenerati, modelli. Il colore salvato è verificato dopo reload, nel pulsante HTML effettivo e nel modulo, chiaro/scuro con testo a contrasto. Blocco degli endpoint di invio nel browser e zero POST invio; nessuna email o modifica a ospiti reali. Le fixture sintetiche sono state rimosse: HMAC di tutte le righe preesistenti delle 66 tabelle e 75 persistenti PASS, senza restore né reset AUTO_INCREMENT; sola eccezione mtime directory dei loghi.

Monitor dopo pulizia: server 918.135 s / 31 campioni / 279 sonde, Mac 921.054 s / 31 campioni / 93 richieste con DNS/TLS ordinari PASS. Zero nuovi errori Laravel/FPM dall’intero primo T0. Soglie Mac 8 GiB / Extra 40 GiB, riserva VM 1 GiB, budget 1800/600/1379/421, disco 36 GiB / XFS / SELinux Enforcing e daily 945ef553 preservati. Il commit documentale conclusivo è distinto dall’applicativo online; checkout originario e lavoro preesistente preservati. Audit generale e questioni DNS/DKIM storiche restano separati; nessun pendente nello scope verificato.

CODICE MODIFICATO: SI. TEST MODIFICATI: SI. DOCUMENTAZIONE MODIFICATA: SI. CONFIGURAZIONE APPLICATIVA MODIFICATA: NO. SCHEMA/DATI REALI MODIFICATI: NO. FIXTURE SINTETICHE: CREATE E RIMOSSE. TEST ESEGUITI: SI. COMMIT/PUSH: SI. INTEGRAZIONE MAIN: SI. DEPLOY: SI. EMAIL/INVII ENTI REALI: NO.
