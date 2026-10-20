# Web Check-in — testi leggibili e palette completa, 20 ottobre 2026

## Stato: PARZIALE — proposta da collaudare, non pubblicata

Base GitHub: a24bb3c8abed427c59e902b9b97e6aca87b36bfb.
Ramo: codex/web-checkin-testi-palette-2026-10-20.
BASELINE IN AUDIT invariata.

## Riscontri e intervento
Verificata direttamente l'interfaccia online: le sezioni ospite sono presenti, ma l'editor dei modelli mostra ancora la sintassi tecnica. Confermati colori fissi per sfondi e bordi nella vista pubblica e nell'email.
La proposta mostra «Nome cliente», «Nome struttura» e gli altri dati automatici in linguaggio leggibile. L'evento formdata riconverte soltanto i sei campi di messaggio nel formato canonico, sia per anteprima asincrona sia per salvataggio/duplicazione modello e condivisione. I modelli rimangono riutilizzabili; nessuna sostituzione con dati nominativi salvati nei modelli.
Aggiunta palette derivata dal colore esistente: sfondo, tono tenue, bordo e inchiostro scuro. Applicata a email e modulo pubblico, intestazioni, link, controlli e calendario. Il colore primario e la funzione di contrasto esistenti sono conservati. La palette viene calcolata dal colore dello snapshot: nessuna nuova chiave nell'identità, nessuna invalidazione generalizzata delle impronte.

## Prova reale esplicitamente richiesta
Richiesta 134, destinatario già configurato nella casella controllata dell'utente. Verificati logo, referente, soggiorno, destinatario e anteprima completa. Personalizzati oggetto, saluto e firma per questa sola comunicazione. Un solo invio, eseguito tramite la conferma del destinatario nell'interfaccia.
Alle 15:01:01 Europe/Rome l'applicazione ha attestato: «Inviato: accettato dal trasporto email; consegna non confermata».
Non verificata la ricezione nella casella Gmail. Questa prova usa l'applicativo già online, NON la nuova palette proposta. Nessun secondo invio automatico. Nessun dato degli ospiti, modello riutilizzabile o colore della struttura modificato; creata la sola comunicazione richiesta con il suo tentativo.

## Verifiche e limiti
- Revisione completa dei quattro diff applicativi.
- node --check /tmp/web-checkin-editor-check.js: PASS di sintassi sullo script editor estratto, con soli valori Blade sostituiti.
- node --check review/tests/Feature/web-checkin-html.playwright.spec.js: PASS di sintassi.
- git diff --no-index --check before review: nessun errore whitespace; exit 1 indica differenze nel confronto no-index.
- Spec HTML esteso a segnaposto leggibili, serializzazione canonica di FormData, sfondi e bordi di email e modulo per colori chiari/scuri.
- PHP, rendering Blade, PHPUnit, Playwright applicativo, test negativi su due tenant, build e C5 NON ESEGUITI: in questa sessione mancano PHP, limactl e accesso al runtime ufficiale. Nessun aggiramento delle guardie e nessun test su DB reale.
- Rischio MEDIO: controllare round-trip dei modelli, modifica/ripresa, validazione delle lunghezze, FormData su browser supportati e contrasto responsive. I segnaposto con caporali sono una sintassi riservata dell'editor; la loro digitazione inserisce un dato automatico.
- Prima del rilascio eseguire nella VM ufficiale le regressioni Web Check-in HTML/comunicazioni/circuito con fixture sintetiche; controllare modello salvato su una seconda richiesta, payload anteprima e salvataggio tradizionale, contrasto e 1440/768/390. C5 solo dopo esito positivo e secondo il protocollo vigente.

## Tenant e sicurezza
Nessuna modifica ad autorizzazioni, route, scope tenant, CSRF, token, SMTP, schema o salvataggio ospiti. Le sostituzioni sono testuali; nessuna interpretazione HTML/PHP introdotta. Le prove dinamiche di regressione restano pendenti.

CODICE MODIFICATO: SI. TEST MODIFICATI: SI. DOCUMENTAZIONE MODIFICATA: SI.
CONFIGURAZIONE MODIFICATA: NO. SCHEMA MODIFICATO: NO. DATI: SI, sola comunicazione/invio richiesti.
TEST FUNZIONALI DELLA PROPOSTA ESEGUITI: NO. CONTROLLI SINTATTICI: SI.
DEPLOY: NO. TRASMISSIONI ESTERNE: SI, un solo invio reale richiesto.
