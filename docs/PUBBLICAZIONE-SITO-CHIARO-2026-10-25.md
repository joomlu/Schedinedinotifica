# Pubblicazione del sito chiaro Tanggo Software — 26 ottobre 2026

**Locale aggiornato; candidato produttivo accettato da C5 e QA online PASS. Monitor finale in corso. BASELINE IN AUDIT invariata.**

Il sito resta parte della stessa installazione Laravel. L’intervento comprende il recupero del design PR28, le correzioni di coerenza, la qualifica isolata, l’aggiornamento Herd, l’integrazione GitHub e il rilascio SPanel. Nessuna nuova installazione o modifica dello schema.

## Versioni e correzioni

PR28 verificata contro GitHub: merge `88dc167aa067dc696420941d6ffe5d2db1ac3c9b`, head design `95580df381d02868031714ef5a9c68e55a6e66a2`. PR29 integra il candidato applicativo **`fa75a1f05ec013e6338ef423f8dc9f5db3e18e11`**, realmente installato in produzione. PR30 e PR31 aggiungono documentazione e il delta operativo del trasporto; non sostituiscono il candidato con un altro applicativo.

Marca Tanggo Software, logo originale e menu uniformati anche nelle tre informative. Corpi legali e dati societari preesistenti preservati byte per byte. Nel catalogo è cambiato soltanto il brand. Tariffe annuali IVA esclusa invariate: 50 € per un appartamento, 100 € per due, 200 € per tre–sette, 320 € complessivi per una–due strutture; oltre tali soglie e per Max Full resta il preventivo personalizzato. Configurazione e servizi ulteriori seguono le condizioni esistenti.

## Qualifica nella VM ufficiale

[Prova isolata](evidenze/sito-chiaro-2026-10-25/verifica.json): CHIARO-R5, 20 prove browser PASS, nove pagine a 1440/768/390 px e menu a 1280/1266/1151/1024 px; build/GEO, 23 guardie e 4412 digest conformi. Prove sito/CRM CHIARO-R4: 25 test PHP e 571 asserzioni PASS, applicabili ai medesimi sorgenti non test della R5. Regressioni CHIARO-R2: 113 test e 1255 asserzioni PASS, con il successivo delta delle informative ricoperto da R4/R5. Esiti distinti, non sommati.

Verificati CSRF, errori, conservazione dei campi, doppio invio, salvataggio CRM, conferma reale, demo a cinque schermate, prezzi/Max Full, tastiera, login e rotte private, immagini decodificate, risorse versionate/MIME, canonical, sitemap, robots e console. I tentativi NONPASS degli adattatori sono conservati; nessun esito parziale è promosso a PASS. Nessuna suite Laravel eseguita sull’host.

## Herd e conservazione dei lavori locali

[https://schedinedinotifica.test/](https://schedinedinotifica.test/) verificato sulla vera applicazione: nove pagine, marca/logo/versione, hash e MIME, token CSRF, login e redirect delle rotte private PASS. Nessun POST locale. Build prodotta nella VM: 3904 file verificati, 4132 file precedenti custoditi. `.env`, ramo precedente e lavori documentali locali preservati; 111 collisioni documentali iniziali custodite e confrontate byte per byte.

Il precedente 404 di `/robots.txt` è stato ricondotto alle due location esatte del solo vhost Herd, con root `/`. Originale custodito; corretta la root verso `public` e applicazione riuscita tramite il comando ufficiale `herd restart nginx --no-interaction`. Accettazione completa successiva PASS, robots HTTP200. Nessuna modifica della configurazione produttiva per questo difetto locale.

## Consenso, backup e PREPARE

Il precedente rifiuto della revisione automatica chiedeva consenso specifico per trasferire backup cifrati contenenti DB, file e configurazione verso Mac/VM/Extra. L’utente lo ha fornito esplicitamente il 26 ottobre: «autorizo el backuppara la publicacion». Nessun aggiramento; il precedente stato bloccato resta nella ricevuta storica.

Il primo PREPARE autorizzato si è fermato al limite globale di 120 secondi, prima di manutenzione o installazione. Qualificato il solo delta di trasporto: quattro flussi con parti da 16 MiB, stesso limite globale comprensivo degli hash finali, lunghezze e file esclusivi verificati, chiusura dei soli figli SSH. Dodici prove di componente PASS e trasferimento integrale di prova di 544582256 byte in 45,322 s. [Sorgenti e prove](operazioni/sito-chiaro-trasporto-2026-10-26/LEGGIMI.md).

Kit nuovo `f03a9cc82f4b151ab9ee8e34e5c0b3a368e289d192c40e5341298b96cf67e4da`. Nuovo PREPARE fresco: 544587408 byte in 45,504 s e recupero indipendente nella VM PASS. Copie cifrate private sul Mac, VM ufficiale e Extra, chiave separata nella VM; integrità e decifratura integrale Extra PASS. Originali, SQL, cifrati, chiavi e diagnostici conservati.

Per la capacità sono stati ritirati soltanto `extracted`, `dbdata` e `db-tmp` di due recuperi propri già conclusi, dopo verifica di 23828/23842 record, metadati, SQL/cifrati/chiave e assenza di processi sui target. Inventari e ricevute conservati. Soglia residua 3215986688 byte e riserva VM di 1 GiB mantenute; nessun backup rimosso.

## C5 produttivo

Il primo RUN si è fermato per il controllo conservativo dell’intero socket FPM precedente, non quieto durante il recupero. Nessuna causa esterna attribuita senza prova. Guardiano: baseline `4a85dea22d600f52c5922c06fc209373d287c9c3` riaperta sana in 41,724 s, prima di execute/DDL. Il ricevente locale ha terminato il recupero, ma il server già fermo non ha accettato la ricevuta: BrokenPipe conservato; quello S0 non è diventato un checkpoint valido. Cifrati custoditi separatamente anche su Extra, guardiano concluso e disabilitato.

Secondo RUN in una nuova finestra distinta, con nuovo piano, T0/S0 e 55 campioni di quiete PASS; nessuna guardia o budget ridotto. **C5 PASS_AGGIORNAMENTO_C5:** S0 indipendente 177,889 s, rilascio 290,396 s, 462 campioni, candidato `fa75a1f0` accettato e barriera assente. Limiti 1800/600/1379/421 s invariati; trasporto 120 s e singola chiamata ricevente 240 s invariati. Zero DDL e nessuna migrazione applicata.

Verifica prima della QA: Git pulito, 296 sorgenti/protetti conformi, Laravel 12.69.3, 159 migrazioni/66 tabelle, DB byte-identico al nuovo S0. Configurazione, APP_KEY, upload e loghi preservati. Nuovo S0 su Extra: integrità e decifratura PASS. Entrambi i guardiani propri inattivi/disabilitati, servizi originali preservati.

## QA del dominio ufficiale e pulizia

[https://schedinedinotifica.tanggo.software/](https://schedinedinotifica.tanggo.software/) serve il candidato `fa75a1f0`, versione pubblica `e9ddcb7200b5f84f55d4ff3a`. [Prova online](evidenze/sito-chiaro-2026-10-25/online.json): nove pagine a 1440/768/390 px, 164 ancore per larghezza, 13 hash di risorse inclusi logo e foto ospitalità, menu/focus/tastiera, immagini decodificate, demo, catalogo/prezzi/Max Full, navigazione reale e reload/back/forward PASS. Nessun overflow o errore JavaScript/HTTP. Ispezionate 33 catture, con inventario SHA256; nove immagini rappresentative versionate.

QA R1 fermata dall’adattatore che tentava lo scorrimento delle immagini nascoste nelle schede demo: zero POST, chiusura con HMAC delle 66 tabelle PASS e nessun record da rimuovere. Esito conservato. Corretto il solo scorrimento delle immagini visibili; tutti e cinque i tab restano verificati nel tour, stesso timeout. R2 usa un nuovo marcatore/runtime e lo stesso applicativo.

R2 PASS con tre POST sintetici, uno per larghezza. Tre richieste e tre attività CRM reali verificate, compresi piano, Max Full, interesse e tariffa canonica server. Rimosse soltanto le righe sintetiche marcate, con ID/HMAC/conteggi e transazione con lock: tutte le 66 tabelle preesistenti identiche, nessun restore o reset dei contatori. Entrambi i runtime propri chiusi dopo la raccolta delle prove. Verifica dopo pulizia: 92 voci persistenti, contenuti e metadati conformi a S0; escluso soltanto mtime delle directory secondo protocollo.

## Monitor e stato conclusivo

Monitor server e Mac avviati dopo QA e pulizia. Il verdetto finale richiede almeno 900 secondi, 31 campioni server/279 sonde e 31 campioni Mac/93 richieste con DNS e TLS ordinari. **Ancora in corso: nessun PASS finale anticipato.**

Daily della VM: HEAD `945ef553aab29ac437ff194f7cfad081fb7ab7c7`, stato Git e `.env` invariati, SELinux Enforcing, disco da 40 GiB. VM lasciata attiva perché era già avviata; alla conclusione saranno chiusi soltanto i presidi propri. Riserve Mac 8 GiB, VM 1 GiB ed Extra 40 GiB conservate nei controlli previsti.

Nessun invio reale a enti, email, account, pagamento o dato ospite impiegato nella QA. Errori intenzionali del modulo e regressioni restano prove isolate; nessuna autenticazione produttiva durante il browser QA. Le verifiche dei canali reali ISTAT e dell’accettazione StayTour restano nello stato preesistente e non ricevono una nuova certificazione da questo rilascio commerciale. **BASELINE IN AUDIT invariata.**
