# Web Check-in: email visibile in modale e azioni ordinate — 20 ottobre 2026

**Applicativo `8edeb15104e8c8a1c9a61e0415ec207e2bacd202` online; C5, QA sintetica, pulizia e monitor finali PASS. BASELINE IN AUDIT invariata.** [Ricevuta](evidenze/web-checkin-email-modale-2026-10-20/rilascio.json), [QA online](evidenze/web-checkin-email-modale-2026-10-20/qa-online.json).

## Percorso per l’operatore

Dall’elenco, «Anteprima e invio» apre Personalizza. Modello, destinatario, oggetto e campi personalizzabili precedono il pulsante «Visualizza email». Questo apre una modale interna ampia con il vero HTML della comunicazione, logo, soggiorno, pulsante e piè di pagina. Desktop e Mobile cambiano la larghezza del messaggio; il contenuto lungo scorre dentro la modale lasciando raggiungibili i comandi.

«Torna alla modifica» mantiene i campi; dopo una modifica occorre generare una nuova anteprima. «Conferma e invia» richiede la conferma del destinatario e usa il tentativo già preparato. Un messaggio già tentato non viene ripetuto dall’anteprima. Nell’elenco «Rivedi email» è separato da «Prepara un reinvio volontario».

«Modifica» resta secondario. «Altre azioni» raggruppa Ospiti, Accesso, Attività e Operazioni; il Cestino è separato in fondo. Effettua check-in e la precedente dicitura Crea schedina richiamavano la stessa conversione: una sola denominazione operativa, conferma e regole conservate. Il dettaglio condivide intestazione e schede Richiesta, Email, Ospiti.

## Implementazione e limiti

La modale riusa il renderer e lo snapshot della comunicazione esistenti; non introduce un secondo modello email. L’endpoint di preparazione risponde con il solo dialogo quando riceve esplicitamente `vista_modale` e una richiesta JSON email; gli altri chiamanti conservano il comportamento precedente. Ogni nuova anteprima personalizzata usa una nuova chiave di preparazione; firma dei campi e blocco durante la preparazione impediscono di confermare un contenuto superato nella stessa schermata. Protezioni server, permessi, tenancy, idempotenza e controlli SMTP restano quelli esistenti.

Il frame ammette la lettura della misura dal contenitore, senza script; è inerte e i collegamenti non attivano il modulo operativo. Il collegamento separato apre l’anteprima ospite riservata già esistente, senza registrare visite. Il frame cresce secondo l’altezza effettiva e il contenitore scorre: messaggi lunghi non vengono troncati da un’altezza fissa.

L’HTML rappresenta il messaggio inviato, senza promettere identica resa in ogni client. L’accettazione del trasporto resta distinta dalla consegna.

## Collaudi e diagnosi

Tutte le suite di collaudo Laravel vengono eseguite nella VM ufficiale, in namespace senza rete esterna, database e checkout temporanei. Il daily `945ef553aab29ac437ff194f7cfad081fb7ab7c7` è preservato. La fixture di intercettazione vive soltanto nel checkout effimero e sostituisce il trasporto SMTP con un trasporto in memoria: nessuna connessione SMTP né email reale.

- R1: due aspettative su etichette precedenti da aggiornare.
- R2: 67 test / 651 asserzioni PHP PASS; browser NONPASS perché la route della fixture veniva preceduta dalla route generica. Corretto l’ordine solo nella fixture temporanea.
- R3: 67 / 651 PASS; browser 390 PASS, 1440 e 768 NONPASS per inizializzazione del modello che sovrascriveva input precoci. Corretto il difetto applicativo disabilitando i campi fino al termine dell’inizializzazione.
- R4: suite completa **265 test / 3.097 asserzioni PASS**, tre scenari nuovi a **1440, 768 e 390 px PASS**. Logo caricato, messaggio lungo, piè di pagina, desktop/mobile, modifica e rigenerazione, invio intercettato, confronto HTML e Cestino verificati. La sola normalizzazione del confronto riguarda il riferimento del logo CID e le terminazioni di riga. Build, GEO e 23 rifiuti di override PASS. Timeout e soglie invariati.

Ispezionate direttamente le schermate desktop, tablet e mobile di personalizzazione, email completa e menu.

- R5 e R7: NONPASS/interrotti. Adattate le interazioni precedenti al menu; l’esecuzione parallela sulla VM da 1,7 GiB RAM è stata un errore operativo, corretto tornando alla sequenza. Cleanup confermato, nessuna modifica alle risorse o alle soglie.
- R6: 67 test / 651 asserzioni e tre scenari nuovi PASS. La successiva revisione, già inclusa in R8, ha corretto l’inizializzazione ripetuta della modale riaperta.
- R8: 21 scenari storici PASS; tre scenari interfaccia NONPASS per attesa generica `networkidle` nel vecchio adattatore fotografico. La diagnosi mostra modale e HTML già renderizzati. Adattate le attese alla modale, senza aumentare timeout.
- R9 finale: 67 test / 651 asserzioni e tre scenari modale PASS. A 768 e 390 px verificati anche chiusura e riapertura della stessa finestra prima dell’unico invio intercettato.
- R10: tre percorsi funzionali PASS; immagini scartate nell’ispezione perché catturate durante la dissolvenza.
- R11 finale: tre percorsi interfaccia e 30 schermate stabili PASS, dopo verifica di opacità, trasformazione e modalità Mobile. Ispezionate direttamente le tre anteprime.

I 24 scenari storici distinti sono coperti da R8 (21) e R11 (3), su applicativo invariato; le ripetizioni non vengono sommate. I 102 sorgenti risultano identici fra workspace, commit e clone ufficiale; 21 immagini finali accettate sono sigillate nella [verifica](evidenze/web-checkin-email-modale-2026-10-20/verifica.json). R4 precede i ritocchi alla denominazione Effettua check-in e ai canali testo/WhatsApp; R9 e R11 qualificano la versione finale pertinente.

## Custodia e pubblicazione

Prima delle modifiche riletti produzione, main e ramo candidato. Conservati i cambiamenti estranei. Recuperata capacità ritirando soltanto 12 cache datadir di recuperi conclusi: dump e riesportazioni sigillati identici, nessun utilizzatore; SQL, cifrati, backup, chiavi ed evidenze conservati. Nessuna crescita della VM.

Nuova C5 completa sul candidato `8edeb15104e8c8a1c9a61e0415ec207e2bacd202`, baseline `42deeaf399910d8e6ac913348306756a9c566277`. PREPARE e S0 nuovi: S0 incrementale sul nuovo PREPARE, entrambi conservati; recupero completo della catena verificato indipendentemente senza rete. Custodia Extra con HMAC/decifratura integrali e chiave separata. S0 130.374 s, RUN 214.687 s; nessuna DDL,159 migrazioni / 66 tabelle. I 161 sorgenti/protetti e 102 sorgenti della task sono conformi. DB byte-identico a S0 prima della QA; 75 persistenti, env, chiave, upload e loghi preservati; guardiano concluso/disarmato/inattivo/disabilitato.

QA online: tre percorsi della nuova modale con HTML lungo, desktop/mobile, modifica/rigenerazione e riapertura; nessun POST d’invio. Sei percorsi singolo/famiglia con bambino/gruppo, tre percorsi interfaccia, clienti/Bozze/tenancy e 24 navigazioni, modelli e accessi scaduti/revocati/rigenerati PASS. Le prove d’invio restano esclusivamente nel trasporto intercettato dell’ambiente isolato; nessuna email reale. [Modale online desktop](evidenze/web-checkin-email-modale-2026-10-20/online-modale-online-email-desktop-inizio-1440.png), [fondo email mobile](evidenze/web-checkin-email-modale-2026-10-20/online-modale-online-email-mobile-390.png), [azioni](evidenze/web-checkin-email-modale-2026-10-20/online-modale-online-azioni-390.png).

Primo adattatore online NONPASS per un selettore che cercava la prenotazione nella riga del Cestino, mentre questa espone codice e referente. Nessuna modifica applicativa: prima fixture rimossa con HMAC su 66 tabelle PASS, tentativo preservato e tutte le prove ripetute su una fixture nuova. Anche il primo adattatore dei modelli si è fermato perché non riapriva la sezione dopo il salvataggio: rimossi solo i due modelli sintetici parziali, conservati i tre iniziali, ripetuto l’intero controllo con esito PASS. [Diagnosi e integrità delle ripetizioni](evidenze/web-checkin-email-modale-2026-10-20/qa-diagnosi-intermedie.json).

Fixture rimosse: HMAC di tutte le righe preesistenti delle 66 tabelle e 75 persistenti PASS. Nessun restore o reset dei contatori AUTO_INCREMENT. Monitor dopo pulizia: server 917,950 s / 31 campioni / 279 sonde; Mac 920,981 s / 31 campioni / 93 richieste con DNS/TLS ordinari PASS. Zero nuovi errori Laravel/FPM dall’intero T0. Risorse e ricevute nel consuntivo; disco VM 36 GiB / XFS / SELinux Enforcing e daily 945ef553 preservati, soglie 8/40 GiB e riserva 1 GiB e budget invariati.

GitHub aggiornato sul solo ramo dedicato. Main non aggiornato: il precedente tentativo era stato rifiutato dalla revisione automatica perché fuori dall’autorizzazione; nessun aggiramento o nuovo tentativo. Il commit documentale conclusivo è distinto dal candidato applicativo online. Audit generale BASELINE IN AUDIT e questioni DNS/DKIM storiche restano separati.

## Evidenze visive accettate

[Modale desktop, inizio del messaggio lungo](evidenze/web-checkin-email-modale-2026-10-20/schermate/email-desktop-inizio-1440.png) · [Piè di pagina in modalità mobile](evidenze/web-checkin-email-modale-2026-10-20/schermate/email-mobile-768.png) · [Modale a 390 px](evidenze/web-checkin-email-modale-2026-10-20/schermate/email-mobile-inizio-390.png) · [Azioni nell’elenco mobile](evidenze/web-checkin-email-modale-2026-10-20/schermate/azioni-390.png).

R4 e R6 precedono l’ultima correzione della riapertura; la qualificazione finale pertinente è R9 (67/651 e tre invii intercettati), R8 (21 scenari storici) e R11 (tre percorsi interfaccia), su applicativo invariato fra R8 e R11. Esecuzioni sovrapposte non sommate. Nessun test Laravel sul Mac; guardie, limiti, timeout e retry invariati.
