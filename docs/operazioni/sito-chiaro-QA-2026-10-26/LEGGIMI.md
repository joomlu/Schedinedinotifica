# QA online del sito chiaro — 26 ottobre 2026

Adattatore effettivamente eseguito nella VM ufficiale dopo C5 e verifica del database identico a S0. La presenza del codice non autorizza una nuova esecuzione produttiva. Applicativo fa75a1f05ec013e6338ef423f8dc9f5db3e18e11, esito online R2 PASS.

Il browser usa Playwright dal clone isolato e richiede un quinto input privato `fixture-privata.json` con le sole proprietà `marker` e `sha`, creato dalla preparazione controllata sul server. Questo input, gli ID CRM e le fotografie HMAC delle tabelle non sono versionati. La chiave e le credenziali non sono mai fornite al browser. Il file rifiuta un rapporto già esistente e non deve essere rilanciato nello stesso runtime.

Sono permessi soltanto HTTPS sull’origine ufficiale, GET/HEAD e un unico POST a `/contatti/richiesta` per ciascuna larghezza: tre in totale, nessun retry. Altre origini/metodi, WebSocket e service worker sono bloccati. Nessun login, acquisto, account, email o invio a enti.

Prima del browser: fotografia HMAC delle 66 tabelle senza scritture. Dopo: verifica delle tre richieste e attività CRM e pulizia delle sole righe del marcatore con ID/HMAC/conteggi, transazione con lock e confronto integrale delle tabelle preesistenti; nessun restore/reset. Raccogliere i risultati anche dopo un NONPASS, chiudere sempre la fixture e solo dopo ritirare il runtime proprio. Modifiche reali concorrenti richiedono riconciliazione, non ripristino del database.

La prima esecuzione è stata fermata su uno scorrimento di immagini delle schede demo nascoste, prima di qualsiasi POST. Risultati conservati e chiusura HMAC66 PASS con zero righe sintetiche. R2 cambia soltanto lo scorrimento in `img:visible`: il caricamento di tutte le immagini resta verificato e il tour visita tutti i cinque tab, con lo stesso timeout. Nuovo marcatore e runtime, applicativo invariato. L’inventario comprende 13 risorse, inclusi logo e foto ospitalità.

[Prova online e inventario delle 33 catture](../../evidenze/sito-chiaro-2026-10-25/online.json). Nove catture rappresentative versionate; tutti i risultati grezzi e le 33 immagini restano nella custodia privata Mac indicata dal rapporto. La QA non estende la certificazione ai canali reali ISTAT/StayTour né sostituisce le prove isolate del modulo.
