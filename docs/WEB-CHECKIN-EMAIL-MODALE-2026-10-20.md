# Web Check-in: email visibile in modale e azioni ordinate — 20 ottobre 2026

**Candidato `69da0a3b9f35d9236bc5bbdd113c5743bb9b9250` qualificato in isolamento; nuova C5 da eseguire.** Produzione ancora `42deeaf399910d8e6ac913348306756a9c566277`; nessuna nuova pubblicazione attribuita a questa correzione. BASELINE IN AUDIT invariata.

## Percorso per l’operatore

Dall’elenco, «Anteprima e invio» apre Personalizza. Modello, destinatario, oggetto e campi personalizzabili precedono il pulsante «Visualizza email». Questo apre una modale interna ampia con il vero HTML della comunicazione, logo, soggiorno, pulsante e piè di pagina. Desktop e Mobile cambiano la larghezza del messaggio; il contenuto lungo scorre dentro la modale lasciando raggiungibili i comandi.

«Torna alla modifica» mantiene i campi; dopo una modifica occorre generare una nuova anteprima. «Conferma e invia» richiede la conferma del destinatario e usa il tentativo già preparato. Un messaggio già tentato non viene ripetuto dall’anteprima. Nell’elenco «Rivedi email» è separato da «Prepara un reinvio volontario».

«Modifica» resta secondario. «Altre azioni» raggruppa Ospiti, Accesso, Attività e Operazioni; il Cestino è separato in fondo. Effettua check-in e la precedente dicitura Crea schedina richiamavano la stessa conversione: una sola denominazione operativa, conferma e regole conservate. Il dettaglio condivide intestazione e schede Richiesta, Email, Ospiti.

## Implementazione e limiti

La modale riusa il renderer e lo snapshot della comunicazione esistenti; non introduce un secondo modello email. L’endpoint di preparazione risponde con il solo dialogo quando riceve esplicitamente `vista_modale` e una richiesta JSON email; gli altri chiamanti conservano il comportamento precedente. Ogni nuova anteprima personalizzata usa una nuova chiave di preparazione; firma dei campi e blocco durante la preparazione impediscono di confermare un contenuto superato nella stessa schermata. Protezioni server, permessi, tenancy, idempotenza e controlli SMTP restano quelli esistenti.

Il frame ammette la lettura della misura dal contenitore, senza script; è inerte e i collegamenti non attivano il modulo operativo. Il collegamento separato apre l’anteprima ospite riservata già esistente, senza registrare visite. Il frame cresce secondo l’altezza effettiva e il contenitore scorre: messaggi lunghi non vengono troncati da un’altezza fissa.

L’HTML rappresenta il messaggio inviato, senza promettere identica resa in ogni client. L’accettazione del trasporto resta distinta dalla consegna.

## Collaudi e diagnosi

Tutte le esecuzioni Laravel avvengono nella VM ufficiale, in namespace senza rete esterna, database e checkout temporanei. Il daily `945ef553aab29ac437ff194f7cfad081fb7ab7c7` è preservato. La fixture di intercettazione vive soltanto nel checkout effimero e sostituisce il trasporto SMTP con un trasporto in memoria: nessuna connessione SMTP né email reale.

- R1: due aspettative su etichette precedenti da aggiornare.
- R2: 67 test / 651 asserzioni PHP PASS; browser NONPASS perché la route della fixture veniva preceduta dalla route generica. Corretto l’ordine solo nella fixture temporanea.
- R3: 67 / 651 PASS; browser 390 PASS, 1440 e 768 NONPASS per inizializzazione del modello che sovrascriveva input precoci. Corretto il difetto applicativo disabilitando i campi fino al termine dell’inizializzazione.
- R4: suite completa **265 test / 3.097 asserzioni PASS**, tre scenari nuovi a **1440, 768 e 390 px PASS**. Logo caricato, messaggio lungo, piè di pagina, desktop/mobile, modifica e rigenerazione, invio intercettato, confronto HTML e Cestino verificati. La sola normalizzazione del confronto riguarda il riferimento del logo CID e le terminazioni di riga. Build, GEO e 23 rifiuti di override PASS. Timeout e soglie invariati.

Ispezionate direttamente schermate desktop e tablet e schermate mobile di personalizzazione, fondo email e menu. R5: regressione storica NONPASS per interazioni precedenti al menu; corretti i selettori, l’apertura del menu e la distinzione fra cronologia comunicazioni e storico check-in. R5 e R7 interrotti ordinatamente dopo aver rilevato contesa sulla VM da 1,7 GiB RAM: avviarli in parallelo è stato un errore operativo, senza modifiche alle risorse o alle soglie. Non vengono conteggiati come PASS. R6: 67 test / 651 asserzioni e tutti i tre scenari nuovi PASS anche dopo la contesa iniziale, con cleanup confermato. R8: 21 scenari storici PASS, tre scenari interfaccia NONPASS per attesa generica `networkidle` dentro il vecchio adattatore fotografico, con modale e HTML già renderizzati nella diagnosi. Adattato il test alla modale con attese esplicite e cattura del riquadro visibile; R10 mirato: tutti i tre percorsi interfaccia a 1440, 768 e 390 px PASS, rispettivamente 22,1 / 21,3 / 22,2 secondi; nessun timeout aumentato. I 24 scenari storici distinti sono quindi coperti da R8 (21) e R10 (3), senza sommare ripetizioni. L’applicativo resta invariato tra R8 e R10; cambia soltanto il test. Revisione successiva: corretta l’inizializzazione ripetuta della modale riaperta, con nuova regressione di chiusura/riapertura prima dell’invio intercettato a 768 e 390 px. R9 finale: 67 test / 651 asserzioni e tre scenari modale PASS, con invio singolo dopo chiusura/riapertura della stessa finestra a 768 e 390 px. Le immagini R10, pur con prove funzionali PASS, sono state scartate nell’ispezione perché catturate durante la dissolvenza; R11 attende opacità, trasformazione e modalità Mobile stabili. R11: tre percorsi interfaccia e 30 schermate stabili PASS, ispezione diretta delle tre anteprime completata. I 102 sorgenti risultano identici fra workspace, commit e clone ufficiale; 21 immagini finali accettate sono sigillate nella [verifica](evidenze/web-checkin-email-modale-2026-10-20/verifica.json). R4 precede il ritocco alla denominazione Effettua check-in e al passaggio ai canali testo/WhatsApp.

## Custodia e pubblicazione

Prima delle modifiche riletti produzione, main e ramo candidato. Conservati i cambiamenti estranei. Recuperata capacità ritirando soltanto 12 cache datadir di recuperi conclusi: dump e riesportazioni sigillati identici, nessun utilizzatore; SQL, cifrati, backup, chiavi ed evidenze conservati. Nessuna crescita della VM.

La pubblicazione deve usare il protocollo C5 completo, con nuovo preflight, PREPARE/S0, recuperi indipendenti, custodia Extra, QA sintetica e monitor finale. Non è autorizzato aggirare il precedente rifiuto automatico su main. Il ramo candidato corrente è `codex/web-checkin-email-modale-2026-10-20`; pubblicazione del ramo dedicato e rilascio C5 ancora pendenti.

## Evidenze visive accettate

[Modale desktop, inizio del messaggio lungo](evidenze/web-checkin-email-modale-2026-10-20/schermate/email-desktop-inizio-1440.png) · [Piè di pagina in modalità mobile](evidenze/web-checkin-email-modale-2026-10-20/schermate/email-mobile-768.png) · [Modale a 390 px](evidenze/web-checkin-email-modale-2026-10-20/schermate/email-mobile-inizio-390.png) · [Azioni nell’elenco mobile](evidenze/web-checkin-email-modale-2026-10-20/schermate/azioni-390.png).

R4 e R6 precedono l’ultima correzione della riapertura; la qualificazione finale pertinente è R9 (67/651 e tre invii intercettati), R8 (21 scenari storici) e R11 (tre percorsi interfaccia), su applicativo invariato fra R8 e R11. Esecuzioni sovrapposte non sommate. Nessun test Laravel sul Mac; guardie, limiti, timeout e retry invariati.
