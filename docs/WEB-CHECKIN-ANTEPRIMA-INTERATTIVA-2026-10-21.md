# Web Check-in — anteprima interattiva e aggiornamento colore, 21 ottobre 2026

## Stato: collaudo isolato PASS — integrazione e C5 pendenti

BASELINE IN AUDIT invariata. Base main e7b12d3ae9f989cfbdd140a2243c5650254ca9a2; produzione riletta in sola lettura 5e01d7b1153daac8de96a6281cd6203ff92c034d. La PR4 è già integrata; questo intervento prosegue sul nuovo ramo codex/web-checkin-anteprima-interattiva-2026-10-21 nel worktree separato. Checkout originario, quattro documenti modificati e tutto il lavoro preesistente preservati.

## Cause e correzioni circoscritte

L’anteprima ospite disabilitava esplicitamente tutto il fieldset, impedendo Esente e Tipo alloggiato. Ora usa il formulario canonico interattivo e il breve avviso «Anteprima interattiva: le modifiche non vengono salvate». Salvataggio e conferma spiegano la dimostrazione senza POST. Anche senza JavaScript il formulario punta a una nuova destinazione autenticata e verificata per struttura, che ignora i dati ricevuti: nessuna persistenza, visita o conversione. Navigazione, calendario, GEO e componenti restano gli stessi del modulo ospite.

Il salvataggio colore era separato dall’editor e usava un POST con navigazione; la firma dell’anteprima non comprendeva il colore salvato. Ora il salvataggio asincrono conserva il testo in modifica, aggiorna la versione del colore e invalida la vista precedente. Una risposta anteprima arrivata dopo il cambio colore viene rifiutata; la nuova preparazione usa l’identità corrente. Gli snapshot persistenti e le email già inviate restano immutati. Errori di colore accanto al controllo, testo italiano e nessuna chiusura in caso di errore.

Intestazione e piè di pagina del modal email hanno limiti e scorrimento propri, lasciando il contenuto nella schermata. L’ispezione visiva a390px ha rilevato Reset GEO tagliato nel Componente: comandi a capo nel solo modulo pubblico, con verifica geometrica. Nessuna modifica ai contratti Questura/ISTAT, allo schema, alla configurazione o al trasporto SMTP.

## Evidenze intermedie e limiti

Solo VM ufficiale schedine-sviluppo-interno, namespace senza rete esterna, database/storage/sessioni effimeri e posta intercettata. R1 riproduce Esente disabilitato; R2 PHP22/226 PASS, browser fermo per opzione Esente mancante nella fixture; aggiunto il solo codice sintetico990. R3 PHP22/226 PASS e anteprima desktop PASS, scenario colore fermo dal messaggio visibile di selezione struttura, da chiudere normalmente nell’adattatore. R5 PHP22/230 e9browser PASS: anteprima temporanea, colore senza ricarica, risposta ritardata, HTML effettivamente trasportato e modali a1440/768/390. Successiva correzione CSS pubblica per Reset GEO e controllo geometrico ancora da qualificare con le prove finali. Nessuna asserzione, validazione, soglia o guardia indebolita. Le vecchie aspettative di campo disabilitato sono aggiornate al nuovo contratto interattivo, aggiungendo il controllo della destinazione dimostrativa.

Le esecuzioni diagnostiche sul vecchio main sono distinte dai collaudi del candidato. Nessun loro NONPASS viene promosso a PASS. Nessun test Laravel/PHPUnit/Playwright sul Mac. Daily945ef553, disco36GiB/XFS e SELinux Enforcing preservati.

## Pubblicazione e invio autorizzati, ancora pendenti

Dopo collaudo esteso e revisione: PR dedicata, integrazione main e C5 con SHA esatto, nuovo preflight, PREPARE/T0/S0 e recuperi indipendenti, custodia Extra, quiete, guardiano e monitor finali secondo il protocollo vigente. Presidio caffeinate temporaneo da terminare alla chiusura. Soglie8GiB Mac/40GiB Extra, riservaVM1GiB e budget1800/600/1379/421 invariati.

Un solo invio reale autorizzato alla casella autorizzata, nuova richiesta sintetica diversa da134, oggetto «TEST Web Check-in — verifica circuito». Non ancora eseguito. L’accettazione SMTP sarà distinta dalla ricezione effettiva. Nessun altro destinatario, ospite reale o ente coinvolto. QA online, pulizia delle sole fixture proprie, conservazione di tutti i dati precedenti, monitor e ricevuta SHA restano pendenti.

## Qualificazione conclusiva — codice definitivo

R9 **355 test PHP / 3624 asserzioni e9browser mirati PASS**, R10 **24browser storici PASS** sullo stesso applicativo, con manifest identici: **33 scenari distinti**, senza sommare ripetizioni. 17guardie,23override rifiutati,buildVite/GEO eBlade PASS; sei regressioni PHP originarie incluse e ricontrollate nel JUnit. 1440/768/390, colori chiari/scuri e tinta intermedia. La correzione di contrasto usa nero soltanto quando né bianco né l’inchiostro precedente raggiungono4.5:1; i colori chiari/scuri già collaudati e gli snapshot persistenti restano invariati. Errori del renderer associati a oggetto/corpo/quattro campi del layout, mantenendo anche il messaggio precedente; nessuna restrizione rimossa. Calendario selezionato realmente, data ripristinata alla ricarica e pulsanti GEO entro la scheda. HTML trasportato identico all’anteprima salvoCID/CRLF, ultimo colore salvato e risposta obsoleta rifiutata.

R0 eR0b sono diagnostici incompleti degli adattatori, non prove accettate del difetto; R0c riproduce la perdita del testo dopo la conferma effettiva del vecchio salvataggio colore. R4 PHP353/3574 e24browser PASS; R7 PHP354/3605 e9browser eR8 24browser PASS prima del correttivo finale di contrasto. Gli esiti intermedi restano conservati e distinti dalla qualificazione R9/R10. R6 era solo predisposta, non eseguita. Nessun timeout, assert o guardrail indebolito. [Sigillo](evidenze/web-anteprima-interattiva-2026-10-21/verifica.json), log e schermate conservati; immagine mobile precedente classificata non conforme.

Integrazione e C5 restano pendenti. Nessun invio reale ancora eseguito; la sola email futura alla casella autorizzata avrà il soggetto concordato. BASELINE IN AUDIT invariata.
