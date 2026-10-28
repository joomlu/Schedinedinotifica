# Login — identità e assistenza

Il login riutilizza il logo originale della home, senza alterarne lettere, colori o proporzioni, con misura desktop allineata alla testata. Titolo «Accesso struttura», etichette a 16px e peso normale, spazi e opzioni più leggibili. Aggiunta assistenza WhatsApp al contatto ufficiale già presente nel sito, con icona riconoscibile e nome accessibile; nessun messaggio inviato.

Ripristinati i testi «Software gestionale per ospitalità, ricezione e adempimenti telematici.» e «Realizzato con ❤️ per il lavoro quotidiano della struttura.». Il cuore usa l’icona locale esistente anche sui browser privi di font emoji. Footer esatto «© 2026 @Schedine di Notifica - Tanggo Platform | Versione 2.0». Autenticazione, rotte, CSRF, recupero, redirect e controlli preesistenti preservati.

Qualifica finale IDENTITA-R2 sul codice 793dbb8 nella sola infrastruttura isolata: 6 test PHP / 48 asserzioni e 5 scenari browser PASS a 1440/768/390/320 px; build, architettura, GEO e 23 rifiuti di override PASS. Verificati accesso con username/email su utenti sintetici, errori reali, CSRF, tastiera, mostra/nascondi, collegamenti e tipografia. Screenshot finali esaminati su desktop, tablet e mobile. Recupero password verificato come pagina/collegamento, nessuna email inviata; nessun telefono fisico o certificazione WCAG globale attestati.

L’utente ha confermato l’immagine del login locale attuale: fotografia della reception conservata, nessun riferimento grafico ancora pendente. Codice salvato su ramo separato nella PR43 in bozza; pubblicazione in produzione non eseguita perché la capacità richiesta dal protocollo C5 non è conforme. Nessun merge o modifica produttiva. PR42 e lavori estranei preservati. Il consuntivo operativo resta nella documentazione interna. **BASELINE IN AUDIT invariata.**

## Allineamento marchio e credito autore — 28 ottobre 2026

Su richiesta successiva e confronto con le schermate del sito: marchio nel login con nome #2b2b2b, peso700, sottotitolo maiuscolo e spaziatura .13em, logo originale invariato. Aggiunto nel footer «Realizzato da Jorge Omar Luccitelli» al collegamento esistente nella home https://www.cv.joomlu.com/, nuova scheda protetta e indicazione accessibile. Copyright invariato. Verifica visiva locale a1440 e390px positiva; nessun POST o modifica della logica di accesso. Adeguato il selettore del test browser al copyright ora racchiuso nello span; la qualifica IDENTITA-R2 sopra si riferisce al codice precedente, non è stata rieseguita per questo delta di presentazione. Nessun deploy, blocco C5 precedentemente documentato invariato e non rimisurato in questa correzione.

## Login con footer pubblico e contatto a sola icona — 28 ottobre 2026

Sostituiti i testi descrittivi della fotografia con la descrizione del gestionale, la dedica con cuore e il copyright/versione richiesti; eliminati i duplicati sotto il modulo. Footer completo ripreso dalla home con marchio originale, navigazione, informazioni legali e collegamento autore. WhatsApp ora flottante, sola icona34px in pulsante64px, numero ufficiale invariato; nome accessibile conservato, protezioni contro sovrapposizioni e compilazione mobile, incluso il copyright. Logica di autenticazione invariata. Aggiornamento dell’icona pubblica separato nella PR42, login nella PR43.

FOOTER-R3 sul codice3fe3454:6testPHP/48asserzioni e5browser PASS a1440/768/390/320 nella sola VM ufficiale isolata; build/architettura/GEO e23rifiuti override PASS. Accesso username/email, errori reali, CSRF, tastiera, visibilità password, link e comparsa dell’icona nel footer dopo uscita dal modulo verificati con dati sintetici. R1/R2 conservati:6PHP/48PASS e3browserPASS ciascuno, due attese mobile errate per pulsante nascosto dalle protezioni; R2 mantieneva il focus nel campo password, R3 corregge soltanto il test. Correzione visiva della sovrapposizione copyright qualificata in R3. Controllo visivo locale home e login desktop/mobile positivo, sintassi JavaScript e copie pubbliche CSS/JS conformi. La suite commerciale della PR42 non è stata rieseguita per il delta visivo; nessuna email di recupero o messaggio WhatsApp inviato. Nessun telefono fisico o certificazione WCAG globale attestati.

Pubblicazione NON eseguita: capacità completa C5 ancora insufficiente, nessun PREPARE/S0/merge/deploy. Produzione letta soltanto, versione precedente e loginHTTP200. **BASELINE IN AUDIT invariata.**


## Qualifica coordinata successiva

COMBINATO-R2 sul codice4707ff2:27PHP/453asserzioni e11browser PASS insieme a Contatti, tre richieste sintetiche fino alla lettura nel CRM. Capacità laboratorio ripristinata con ampliamento autorizzato, pubblicazione ancora sospesa perché il recupero indipendente preliminare non termina nel budget vigente. Nessun nuovo PREPARE/merge/deploy e nessun invio reale.
