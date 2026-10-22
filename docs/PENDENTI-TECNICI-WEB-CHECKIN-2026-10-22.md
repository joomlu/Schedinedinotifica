# Pendenti tecnici Web Check-in — 22 ottobre 2026

**BASELINE IN AUDIT.** Solo test, diagnostica e documentazione nel codice; nessuna modifica applicativa, di schema o di dati operativi. PHP della VM allineato e configurazione DKIM/DMARC completata separatamente. Nessun nuovo invio reale e nessun deploy applicativo necessario. [Riepilogo pubblicabile](evidenze/pendenti-tecnici-web-2026-10-22/riepilogo.json).

| Pendente | Causa comprovata | Azione realizzata | Evidenza | Stato |
|---|---|---|---|---|
| Timeout Mac al campione 19 | Istante esatto e fasi non registrati; nessuna causa attribuibile | Correlati registri disponibili, DNS/TLS e 279 nuove sonde; diagnostico con timestamp/fasi, timeout 15 s invariato | Diagnostici integrali custoditi privatamente | **VERIFICATO SENZA GUASTO RIPRODOTTO, CAUSA NON DETERMINATA** |
| Deprecazioni PHPUnit | Cinque metadati doc-comment nei tre test | Attributi DataProvider; stessi 404 identificativi, nessuna esclusione | Diff e riepilogo | **RISOLTO E VERIFICATO** |
| SyntaxWarning Python | Backslash PHP non escapati nella stringa Python | Escape corretti con PHP generato invariato;17 guardie con SyntaxWarning trattato come errore | Diff e riepilogo | **RISOLTO E VERIFICATO** |
| PHP VM | Versione precedente differente dalla produzione | Nove soli pacchetti aggiornati;18 RPM custoditi e verificati; configurazioni e moduli conservati | Riepilogo; inventari integrali privati | **RISOLTO E VERIFICATO** |
| SPF, DKIM e DMARC: configurazione | Chiave firmataria e due TXT mancanti | SPF unico preservato, pubblicati DKIM e DMARC, chiave corrispondente leggibile da Exim | Letture autoritative/resolver e verifica chiave custodite privatamente | **RISOLTO E VERIFICATO** per la configurazione |
| Firma effettiva ricevuta | Il messaggio storico non era firmato; nuovo invio non autorizzato | Predisposta prova sintetica separata, senza richieste esistenti | Intestazioni storiche filtrate custodite privatamente | **PENDENTE DI ACCESSO O AUTORIZZAZIONE** per un nuovo messaggio |

## Collaudo e correzioni

Solo nella VM ufficiale: **404 test PHP, 3.992 asserzioni, 33 scenari browser distinti a 1440/768/390 px**, 17 guardie, 23 override rifiutati per runtime,buildVite/GEO/Blade superati. Zero errori/fallimenti/skip e deprecazioni PHPUnit finali. Manifest126 sorgenti host/runtime identico. Parziale/ripresa, componenti, GEO, editor/FormData/modelli, palette, modali, anteprima temporanea, invio intercettato, reception/Storico e isolamento verificati dalle suite pertinenti.

Conservati i NONPASS: prima un PATH privo di Node, poi fixture Componenti senza PDO che rifiutava la lettura della Francia. Corretto il PATH dell’adattatore e aggiunta la sola risposta query/binding attesa, mantenendo tutti gli altri rifiuti e aggiungendo due asserzioni su nome e selezione leggibili. Nessuna modifica applicativa.

Nove browser intermedi:7 fallimenti/2 successi per attese scadute. La pressione di memoria proveniente dal GC automatico avviato dal fetch della task è stata identificata e rimossa annullando il solo worker, preservando riferimenti/pack/lavoro locale. Ripetizione invariata9/9 e poi24/24. Il riavvio diagnostico della VM da solo non aveva risolto. Nessun timeout o test indebolito. Quel GC è successivo al timeout Mac originario: **non ne dimostra la causa**. La revisione automatica aveva rifiutato un avvio con opzione di arresto anticipato; rimosso solo quel parametro dall’adattatore, senza modificare il runner.

Il monitor storico non permette di recuperare retroattivamente l’istante esatto del guasto. I registri consultati non mostrano errori nella finestra stimata; statistiche aggregate non escludono eventi brevi. Mancano le fasi curl del campione fallito e l’identificazione certa del percorso client. Una ripetizione positiva non prova una risoluzione causale. Il nuovo diagnostico conserva solo timestamp e misure DNS/TCP/TLS/TTFB, senza corpi, cookie o credenziali.

Versioni finali PHP CLI/FPM **8.3.36** sia nella VM sia in produzione. Restano differenze ARM/x86, CLI/FPM, moduli aggiuntivi e limiti: non dichiarata identità dello stack. FPM produttivo e restrizioni preservati. I download DNF erano stati cancellati come cache dopo la transazione: recuperati e confrontati con i digest originali, ora custoditi in archivi indipendenti. Restano warning MariaDB/bootstrap e Vite/Sass/font/chunk, estranei alle due cause corrette; non silenziati.

## Posta, conservazione e pubblicazione

SPF unico coerente, TLS e SMTP AUTH/NOOP verificati senza messaggi. Accesso Cloudflare completato personalmente dall’utente; aggiunti soltanto i due TXT del sottodominio. Pubblica RSA2048 e privata corrispondenti, lettura con identità Exim verificata, nessuna chiave privata esportata. Record preesistenti e configurazioni applicative/FPM/Exim preservati. DMARC p=none senza nuovi destinatari di report. Non è ancora una prova di DKIM-Signature ricevuta o di DMARCpass Gmail. Gli header storici attestano soltanto SPFpass e assenza di firma.

Fixture esclusivamente sintetiche e temporanee, rimosse; nessuna richiesta esistente reinviata, mai 134. VM restituita spenta come all’inizio, senza crescita/tuning e con riserva rispettata. Lavoro preesistente preservato tramite confronto di HEAD/ramo/hash; connettività Git verificata. SHA applicativo precedente riconfermato tramite sonda privata firmata nel pool FPM reale e lettura Git: nessun nuovo deploy applicativo.

Il primo push è stato rifiutato dalla revisione automatica per l’esposizione delle evidenze operative in un repository pubblico. Nessun push di quel commit eseguito. Commit integrale e prove preservati privatamente; questo ramo deriva direttamente dalla base e include soltanto test, diagnostico e questo riepilogo senza log/configurazioni/IP di produzione. Il sigillo del manifest privato è nel riepilogo, per la tracciabilità senza pubblicare il deposito operativo.

Monitor successivo al DNS: in corso, da completare prima dell’integrazione. PR e commit finali saranno registrati nella ricevuta locale e nel consuntivo finale.

## Audit generale separato

**BASELINE IN AUDIT** invariata. Questura richiede convalida mapping/cataloghi/casi e accettazione/ricevuta dell’ente con collegamento autorizzato; ISTAT richiede configurazioni/codici assegnati,dataset/periodi/rettifiche e conformità/accettazione dei file; Tassa richiede fonti,categorie,periodi,profili e riconciliazione con conferma operatore. Le vecchie quantità di configurazioni o migration mancanti non sono riproposte come attuali senza verifica. Nessuna certificazione o trasmissione dedotta dai test; nessun cambiamento funzionale a questi moduli.
