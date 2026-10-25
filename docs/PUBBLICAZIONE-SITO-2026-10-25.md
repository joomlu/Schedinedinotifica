# Pubblicazione del sito Tango Soft Pro — 25 ottobre 2026

**Sito pubblicato e verificato online. BASELINE IN AUDIT invariata.**

Il sito ufficiale è [schedinedinotifica.tanggo.software](https://schedinedinotifica.tanggo.software/), nello stesso progetto Laravel. SHA applicativo servito: `1edf049550e624d393d4d8278f3ea541b89dcdf6`. [PR23](https://github.com/joomlu/Schedinedinotifica/pull/23), revisionata senza rilievi e integrata dopo il messaggio umano «publica». Il successivo consuntivo documentale è distinto dallo SHA applicativo servito.

## Contenuti pubblicati

Cinque pagine: home, funzionalità e moduli, demo, prezzi, contatti. Identità Tango Soft Pro, presentazione fotografica navy/turchese, tre immagini illustrative e cinque schermate autentiche con dati dimostrativi. Il tour interattivo mostra Arrivi, preparazione Web Check-in, compilazione dell’ospite, verifica alla reception e contesto fiscale; non viene presentato come video.

I testi descrivono Arrivi, ospiti, Web Check-in, schedine, Alloggiati Web, ISTAT/Ross1000, Tassa di soggiorno, importazioni, stampe, storico e accessi. Configurazione, credenziali, abilitazioni e accettazione dell’ente restano requisiti distinti. Nessuna promessa di copertura universale o nuova certificazione.

Catalogo unico annuale, IVA esclusa, approvato dal proprietario: Solo 50 € per 1 appartamento, Duo 100 € per 2, Multi 200 € per 3–7; oltre 7 preventivo. Strutture 320 € complessivi per 1 o 2 strutture; oltre 2 preventivo. Max Full è aggiuntivo, con prezzo e condizioni operative personalizzati. Eliminati dal sito corrente i precedenti riferimenti a prova gratuita,400€ e60camere, senza riscrivere contratti o storico.

Selezione piano/Max Full trasmessa al CRM; riepilogo ricostruito dal server, senza fidarsi dei prezzi client. Richiesta e attività salvate in transazione. Il modulo non crea account, acquisti o pagamenti. Accessi, URL esistenti e alias preservati; canonical sul dominio ufficiale, titoli, descrizioni, navigazione, ancore e risorse aggiornati. Nessuna nuova DDL, dipendenza, configurazione SMTP/DNS/vhost o logica fiscale/statistica.

## Integrazione e qualifica isolata

Worktree web e worktree di pubblicazione distinti; checkout originario fermo a `6a652b72d9db91c1e25c855ed34a67054eb3a8d0` e modifiche estranee preservati. Base main9724daab; integrazione9da76e3f dei soli30 file sito e20 documentali, applicazione identica al candidato approvato d8309882. Tutti41 file main-only, inclusi ISTAT, preservati. Conflitti soltanto documentali risolti mantenendo gli storici. Revisione indipendente:30/30 blob e mode sito identici,50 file allowlist,41/41 main-only intatti.

[Qualifica INT1](evidenze/sito-rilascio-2026-10-25/verifica.json): **183 test PHP /1.879 asserzioni e9 percorsi browser PASS**, build/GEO,23 rifiuti di override e4.396 digest conformi. Solo VM ufficiale con DB/filesystem effimeri e servizi finti; sei percorsi sito e tre ISTAT. Il PASS del precedente ramo web resta distinto. Nessuna esecuzione Laravel sul Mac.

## Rilascio C5

[Ricevuta finale aggregata](evidenze/sito-rilascio-2026-10-25/rilascio.json). PREPARE fresco, nuovo S0 e recuperi indipendenti completi PASS. Custodie cifrate PREPARE/S0 su Extra autorizzate espressamente dal proprietario, con integrità e decifratura integrale verificate; chiave separata nella VM. Il S0 differenziale conserva la dipendenza dalla base PREPARE, custodita insieme nella cartella dedicata, senza pretendere che il solo delta sia un backup autonomo.

S0 **146,494s**; RUN **250,491s**,481 campioni. Candidato accettato e concluso, zeroDDL;159 migrazioni e66 tabelle. Guardiano concluso, disarmato, inattivo e disabilitato. Git pulito, FPM e9 sonde conformi;279 digest e sorgenti ISTAT preservati. DB byte-identico al nuovo S0 prima della QA. Nessun invio a enti o email reale.

Budget1800/600/1379/421, trasferimento120s e ricevente240s invariati. Riserve VM1GiB/Mac8GiB/Extra40GiB rispettate. Minimi campionati: VM1,426,968,576byte e Mac12,316,880,896byte. Il campionamento ha una pausa dichiarata mentre la VM era spenta in attesa del consenso; nessuna continuità inventata.

## QA online e pulizia

**R3 completo PASS:** cinque pagine a 1440/768/390,11 digest risorse per larghezza, catalogo, listino, Max Full, tour, menu da tastiera, FAQ, ancore, assenza di overflow ed errori JS/HTTP. Conferme dei moduli, focus, Escape e ripristino del form verificati.21 schermate ispezionate, senza difetti visivi concreti.

Tre richieste CRM sintetiche e tre attività verificate: piani Solo/Strutture/oltre2 con Max Full e riepiloghi canonici. Rimozione dei soli record propri nella stessa transazione con blocco delle righe, HMAC, ID e conteggi esatti. Tutte66 tabelle identiche prima/dopo; nessun restore o reset dei contatori.92 persistenti conformi al S0; escluso dal confronto soltanto mtime delle directory secondo protocollo, senza upload o loghi creati dalla QA sito. HMAC per tabella non equivale a snapshot globale: eventuali scritture reali concorrenti richiederebbero riconciliazione, qui nessuna differenza rilevata.

R1/R2 NONPASS conservati: il primo cercava un link accessibile nel menu ancora nascosto, il secondo seguiva il nome «Apri menu» dopo il cambio corretto in «Chiudi menu». Corretto esclusivamente lo script, mantenendo tutte le asserzioni e timeout; R3 interamente ripetuto. Ciascun tentativo precedente chiuso con rimozione di1 richiesta/1 attività e HMAC66 PASS. Tutti e tre i runtime browser propri rimossi; nessuna modifica applicativa dopo lo SHA pubblicato.

Monitor dopo **tutte** le pulizie: server **917.677s /31 campioni /279 sonde**, Mac **920.992s /31 campioni /93 richieste HTTPS**, PASS. DNS/TLS ordinari sul Mac, senza forzare IP o certificati. Zero nuovi errori Laravel/FPM dall’intero T0 del RUN, senza dichiarare l’intera giornata priva di errori.

## Diagnostici e ambiente preservato

Prima custodia Extra bloccata dalla revisione automatica per consenso specifico non chiaro; nessun aggiramento. Dopo la risposta umana esplicita sono stati ripetuti preflight, gate e controlli di stabilità prima della ripresa.

Capacità recuperata ritirando soltanto estratti/dbdata/db-tmp di due ricostruzioni temporanee già chiuse, dopo verifica completa di sigilli, file, ridump, metadati e quiescenza. Originali, cifrati, SQL, chiavi, manifest e log preservati. Nessuna nuova deduplicazione o crescita del disco in questo rilascio.

Primo avvio della ripresa con errore ripetuto del forwarder vsock Lima: arresto ordinato e riavvio riuscito. Registro compresso conservato e riletto integralmente con SHA e dimensione identici. Primo GET urllib con UA predefinito403 conservato; successive cinque GET curl e browser200. Una scheda browser inizialmente mostrava una copia HTML precedente: ricarica ordinaria dell’URL ufficiale corretta, nessun difetto origin dimostrato e nessun intervento cache applicato. Intestazioni con cookie escluse dalle evidenze pubbliche.

VM40GiB restituita **spenta**, daily945ef553, DB logico,84 file protetti e SELinux Enforcing invariati. Soli presidi propri conclusi; recuperi e backup preservati. Anteprima locale63351 conservata come strumento di revisione con contatto simulato esplicito, distinto dal backend reale pubblicato.

## Dati ancora da confermare

Dati societari e Partita IVA, informativa privacy, recapiti aggiuntivi e condizioni operative di Max Full. Nessun dato inventato. Nessun video prodotto: disponibile il percorso interattivo con schermate autentiche. Accettazione StayTour e stato del circuito statistico reale restano quelli documentati dalle rispettive verifiche, senza nuova attestazione esterna derivata dal sito.
