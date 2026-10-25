# Pubblicazione sito Tango Soft Pro — 25 ottobre 2026

**QUALIFICA COMBINATA PASS; PUBBLICAZIONE AUTORIZZATA, RILASCIO C5 DA ESEGUIRE. BASELINE IN AUDIT invariata.**

L’utente ha approvato la pubblicazione con il messaggio «publica» nella chat principale dopo la consegna dell’anteprima63351. Il messaggio umano è stato riscontrato direttamente con lettura della chat, separatamente dall’incarico inoltrato. Scope: sito approvato, passaggi Git necessari nel repository pubblico noto e rilascio secondo il protocollo vigente; nessuna trasmissione statistica, modifica dati ospiti, DDL o variazione SMTP/DNS/vhost.

## Integrazione e revisione

Nuovo worktree `pubblicazione-sito-2026-10-25`, ramo `codex/pubblicazione-sito-2026-10-25`, base main `9724daab02ff6ebac5bc68afb48da5f60193ca6b`. Commit integrato `9da76e3f`:30file sito e20documentali, con applicazione identica al candidato approvato `d8309882`. Nessuna importazione dell’intero vecchio ramo:41file main-only preservati, inclusi tutti i sorgenti ISTAT. Due conflitti documentali risolti mantenendo lo storico R4 e l’intero suffisso corrente del Maestro con il rilascio ISTAT. Nessun conflitto applicativo. I checkout originario e anteprima restano preservati.

Revisione indipendente del secondo agente:30/30blob e mode sito identici,50file allowlist esatti,41/41main-only intatti, specchiCSS/JS identici, zero rilievi concreti bloccanti. Catalogo escapato/failclosed, piano validato e riepilogo ricostruito sul server, CRM transazionale e limiter dedicato riscontrati; esclusione CSRF del contatto preesistente invariata, nessuna nuova attestazione generale di sicurezza.

## Qualifica integrata

[Ricevuta](evidenze/sito-rilascio-2026-10-25/verifica.json). **INT1:183test PHP/1.879asserzioni e9percorsi browser PASS (1,2min),23override rifiutati, build/GEO e4.396digest conformi.** SoloVM ufficiale, DB/filesystem effimeri, fixture sintetiche e servizi finti. I PASS TSP2 del vecchio ramo restano distinti e non sostituiscono questa nuova prova.

```sh
python3 /private/tmp/sito-rilascio-20261025/collaudo.py INT1 --phpunit tests/Feature/PublicSiteRenewalTest.php tests/Feature/IstatConformitaTest.php tests/Feature/IstatCycleTest.php tests/Feature/IstatTransmissionSecurityTest.php tests/Feature/StrutturaAuthorizationTest.php tests/Feature/QuesturaTransportGuardTest.php tests/Feature/QuesturaPayloadCoherenceTest.php --fixtures tests/Isolation/istat-cycle-fixtures.php --playwright tests/Feature/public-site-renewal.playwright.spec.js tests/Feature/istat-cycle.playwright.spec.js
```

Tre percorsi ISTAT con trasportoOFF e storico; sei percorsi sito con cinque pagine1440/768/390, prezzi/MaxFull, navigazione, errori, selezioni e formCRM sintetico. Nove combinazioni modulo aperto/errore422/reset conformi alla viewport. Nessun nuovo invio a enti o destinatari reali. Nessuna modifica a migrazioni, configurazioni, dipendenze o logica statistica/fiscale. Sorgenti collaudati conservati dopo gli aggiornamenti documentali.

## Preflight e rilascio pendente

Lettura server:HEAD `a90d63aa6bd85f668d1ba61a4a5d776e5529cb98`, Git pulito, precedente candidato accettato/concluso e FPM attivo; spazio86.264.463.360byte. Primo comando di sola lettura fallito perché PATH ristretto escludeva runuser, ripetuto con ambiente normale senza mutazioni. VM40GiB avviata dopo gateMac8GiB/Extra40GiB e UUIDExtra verificato; daily945ef553/SELinuxEnforcing/DBlogico e84protetti identici al precedente consuntivo.

Restano PR/revisione/merge e nuovo PREPARE/S0/C5 con recuperi indipendenti, custodie Extra e riserve invariate, poi QA online e monitor900s. Nessun vecchio backup o PASS viene promosso a prova corrente. Il simulatore locale non fa parte del rilascio: si distribuiscono il controllerCRM reale e le risorse Laravel qualificate. I dati societari/privacy/recapiti mancanti non vengono inventati; contenuto e listino sono quelli approvati dall’utente.
