# Pubblicazione del sito chiaro Tanggo Software — 25 ottobre 2026

**IN CORSO; BASELINE IN AUDIT invariata.**

Incarico esplicito: recupero GitHub, correzioni, qualifica isolata, aggiornamento Laravel locale/Herd e produzione SPanel tramite C5. Nessuna nuova installazione, DDL, trasmissione enti o modifica dei dati clienti autorizzata.

GitHub conferma PR28 integrata, merge `88dc167aa067dc696420941d6ffe5d2db1ac3c9b`, head design `95580df381d02868031714ef5a9c68e55a6e66a2`. Fetch completato: main coincide con il merge. Checkout operativo sul ramo precedente `codex/web-checkin-email-modale-2026-10-20`, HEAD `6a652b72`, con modifiche documentali e numerosi file preesistenti preservati. Candidato nel worktree separato `/private/tmp/schedine-sito-chiaro-20261025`, ramo `codex/pubblicazione-sito-chiaro-20261025`.

Produzione letta via SSH nel percorso ricavato dal piano C5 esistente: `4a85dea22d600f52c5922c06fc209373d287c9c3`; dominio ufficiale HTTP200, contenuto precedente con marca Tango Soft Pro. Herd `https://schedinedinotifica.test/` HTTP200 con vecchio sito Tanggo Platform 2.0. Nessuno dei due ambienti è ancora attestato aggiornato.

Allineati marca e logo di note-legali/privacy/cookie al design Tanggo Software; mantenuti corpo delle informative e dati Experience S.r.l. Catalogo: cambiato soltanto brand, prezzi/contratti invariati. Aggiornate le asserzioni dei contenuti commerciali modificati dalla PR28 e aggiunti controlli di logo/marca/font per ogni pagina. Nessuna modifica al backend applicativo.

Le qualifiche storiche R17 non sono promosse a prova del nuovo candidato. Avviata nuova qualifica nella VM ufficiale con DB effimero, namespace di rete, build/GEO e 23 guardie override. Prove, verifica visuale, C5, QA online/pulizia e monitor restano da consuntivare. La documentazione locale registra un PREPARE precedente interrotto e un riavvio respinto dalla revisione automatica per consenso al trasferimento di backup produttivi: fatto storico, non esito di un nuovo tentativo in questa attività.

## Qualifica e revisione del 26 ottobre 2026

CHIARO-R1: 113 prove PHP, 1.253 asserzioni, un fallimento sull'aspettativa del vecchio testo home; browser non avviato. Asserzioni aggiornate al design PR28, senza riduzione dei controlli.

CHIARO-R2: 113 prove PHP/1.255 asserzioni PASS, build/GEO e 23 guardie override PASS. Browser 13/20 PASS; sette arresti degli adattatori: Max Full cercato nel menu anziché nel footer (tre larghezze), conteggio quattro figure anziché cinque (tre larghezze), vecchia foto hero attesa sullo sfondo (desktop). Adeguati i selettori e verificata la nuova foto ospitalità; nessun timeout ampliato o controllo rimosso. Nuova esecuzione browser CHIARO-R3 in corso.

Ispezionate le nove pagine a 390 e 768 px, compresi logo, titoli e informative. Menu delle informative uniformato alle nove voci delle altre pagine; corpi legali sempre byte-identici a main. Verifica finale mirata ancora richiesta su questa ultima modifica. Nessun esito parziale promosso a pubblicazione.

## Qualifica conclusiva del candidato chiaro

CHIARO-R3: tutti20browserPASS. CHIARO-R4:25PHP/571asserzioniPASS; browser3/6PASS, solo confronto nuovo menu fermato dalla voce corrente Contatti non inclusa nell’atteso. Corretta esclusivamente l’asserzione; nessuna ulteriore modifica applicativa. CHIARO-R5 sul candidato `c5f5d0509293bc1f6d699695618a5843d05e8952`:20browserPASS, nove pagine×1440/768/390 e menu anche1280/1266/1151/1024, build/GEO e23guardiePASS.4412digest conformi. PHP sito/CRM della R4 applicabile con tutti i sorgenti non test byte-identici; regressioni113/1255R2 conservate e non sommate.

Controllati errori/oldinput/CSRF/doppio invio/CRM sintetico reale, demo a cinque schermate, prezzi/catalogo/MaxFull, login e rotte private, ancore, hash/MIME, sitemap/robots e console. Ispezionate le catture alle tre larghezze; cinque figure ampliamenti per larghezza decodificate e viste. Informative e dati Experience S.r.l. byte-identici; catalogo cambiato nel solo brand. Qualifica vincolata ai sorgenti nella [ricevuta](evidenze/sito-chiaro-2026-10-25/verifica.json). C5 e pubblicazione ancora pendenti.

## Aggiornamento locale completato; produzione bloccata prima del PREPARE

PR29 integrata in `fa75a1f05ec013e6338ef423f8dc9f5db3e18e11`; tutti47sorgenti sigillati identici alla qualifica. Checkout Herd avanzato a main senza cambiare il puntatore del precedente ramo, quattro documenti locali modificati preservati e111collisioni documentali custodite con confronto dei byte. `.env` byte-identico, nessuna migrazione o modifica di dati clienti. Asset generati nella VM ufficiale:3904file verificati;4132file della build precedente custoditi. Nessuna suite Laravel sull’host.

Accettazione reale [https://schedinedinotifica.test/](https://schedinedinotifica.test/):nove pagineHTTP200 con marca/logo/versione uniforme, cinque risorse con hash/MIME identici ai sorgenti, tokenCSRF presente, login e rotte private conformi; nessunPOSTlocale. `/robots.txt` originariamente404 pur restituendo i byte corretti: causa dimostrata nella location esatta NginxHerd con root `/`. Corretta soltanto la root delle due location del vhost locale, originale custodito. Controlli sintattici positivi ma prime verifiche complete non riuscite per PID/logroot; originale ripristinato dopo ciascun tentativo. Applicazione tramite comando ufficiale Herd `restart nginx --no-interaction` riuscita, nuova verifica completaPASS con robots200. Configurazioni produttive invariate.

Nuovo kitC5 depositato e preflightPASS perfa75a1f0, baseline4a85dea2:296sorgenti/protetti, Gitpulito,FPMattivo,novesonde, infrastruttura/chiavi originali preservate, nessunaDDL prevista. L’avvioPREPARE è stato respinto dalla revisione automatica prima dell’esecuzione. Motivo testuale: «Il PREPARE trasferisce backup cifrati potenzialmente contenenti dati produttivi sensibili dal server verso custodie locali/VM; l’autorizzazione alla pubblicazione e alla conservazione dei dati non specifica questo payload né tali destinazioni.». Nessun aggiramento, trasferimento del nuovoPREPARE, manutenzione,S0,RUN oQAproduttiva. Richiesto consenso specifico per cifrati conDB/file/config nelle custodie privateMac,VMufficiale edExtra, chiave separataVM.

Rilettura successiva: dominio ufficialeHTTP200, SHAeffettivo `4a85dea22d600f52c5922c06fc209373d287c9c3`, Gitpulito,FPMattivo e nessuna manutenzione. ProduzioneNONaggiornata al nuovo design. QA/pulizia/monitor del nuovo candidato restano pendenti insieme al C5; iPASS storici non li sostituiscono. [Ricevuta di stato](evidenze/sito-chiaro-2026-10-25/stato-rilascio.json). Il commit conclusivo documentale resta distinto dall’applicativo candidato. BASELINE IN AUDIT invariata.
