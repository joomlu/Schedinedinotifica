# Correzione mirata del periodo dell’imposta — 12 ottobre 2026

Stato: **VERIFICATO nelle prove isolate; rilascio in preparazione**. Progetto **BASELINE IN AUDIT**. Autorizzazione della task: soli periodo/impostazioni Tassa, test, Maestro, commit/push e pubblicazione conservativa mediante procedura esistente; nessun invio enti, dato fittizio produttivo o cambio infrastruttura.

## Causa accertata e provenienza

`TassaDiSoggiornoController::edit` legge il record della struttura da `tassa_di_soggiorno`, senza salvarlo nel ramo automatico. «Valore salvato» arriva da `valoriConfigurazione`, campi `inizio`/`fine` di quel record; non dalle date di apertura. Il confronto e il riquadro fiscale arrivano da `profiloFiscaleBellaria` → `risolviProfiloFiscale`, selezionato per Comune, categoria e data/anno. Il solo profilo certificato attualmente presente nel servizio è quello Bellaria alberghi 2026. Non sono stati aggiunti Comuni, profili, tariffe o anni normativi.

Il calcolo Bellaria risolve già un profilo per ciascuna notte e applica le sue date inclusive, escludendo la notte della partenza. Il difetto era un controllo preliminare: `validaConfigurazioneBellaria` pretendeva che le date del record legacy coincidessero col profilo; `configurazioneAutomatica` trasformava la discordanza in blocco e richiesta di riallineamento. Una vecchia configurazione annuale o con un altro periodo impediva quindi il calcolo stagionale corretto, nonostante la fonte comunale fosse disponibile. L’etichetta «Differente / derivato dal profilo» confondeva differenza informativa e blocco.

Nessuna dipendenza dalle date di apertura è stata trovata nel percorso fiscale esaminato. Non si attribuisce l’origine storica dei valori salvati all’apertura: il codice corrente non lo dimostra. Apertura/chiusura restano usate dai flussi statistici esistenti, estranei alla correzione.

## Modifica minima

Solo servizio fiscale e vista Configurazione: le date legacy non sono più una causa di blocco. Il periodo effettivo viene dal profilo comunale pertinente all’anno/notte; il record originale resta intatto, senza migrazioni o riallineamenti automatici. Anche un record con sole date o date incomplete non sostituisce il profilo. Importi, esenzioni, limiti ed età mantengono i controlli attuali; discordanze economiche/età restano bloccate con il riallineamento esplicito già esistente e le autorizzazioni originali.

La vista indica Comune, anno e «Periodo di applicazione dell’imposta», dal/al; la colonna cita esplicitamente profilo comunale e anno. Spiega che le vecchie date sono conservate e non applicate: non occorre copiarle. La sola discordanza di date non produce avviso di blocco né pulsante obbligatorio di riallineamento. Profilo annuale mancante/ambiguo continua a bloccare: nessun ripiego su apertura o anno precedente. Gli altri Comuni conservano il percorso esistente; non viene inventata una regola universale.

Ricevute/export consolidati e conteggi storici non vengono riscritti. Le elaborazioni correnti riprendono con il profilo pertinente; i controlli sullo storico sono regressioni, non una rielaborazione produttiva. Nessuna modifica a ruoli, permessi, controller, route, schema, importi o altri moduli.

## Verifiche effettive

VM esistente `schedine-sviluppo-interno`, PHP8.3.33/MariaDB11.8.9/Laravel12.69.3, checkout temporaneo, DB effimero senza dati operativi, namespace senza rete, kernel/middleware reali. Comandi:

```sh
python3 scripts/development/ambiente-interno.py stato
python3 scripts/development/ambiente-interno.py sincronizza
python3 scripts/development/ambiente-interno.py test tests/Feature/TassaPeriodoComunaleTest.php tests/Feature/TassaBellariaConfigurazioneAutomaticaTest.php tests/Feature/TassaBellariaAnteprimaTest.php tests/Feature/TassaBellariaRiallineamentoTest.php tests/Feature/TassaBellariaCompletenessTest.php tests/Feature/TassaBellariaVersionamentoTest.php tests/Feature/TassaBellariaEtaCsvTest.php
```

Esito finale: **53 prove selezionate, 51 PASS, 1.546 asserzioni, 2 SKIP**. Le due prove immagini richiedono GD, assente: non eseguite e non dichiarate PASS; nessuna installazione per ampliare lo scope. Build asset e GEO immutabile PASS, identità runtime/DB/HTTP e 23 override respinti PASS. Temporanei del singolo test fermati/rimossi.

Prove specifiche: apertura annuale 01/01–31/12 e stagionale 01/03–15/11 con imposta del profilo 01/06–30/09; vecchie date annuali conservate, nessun falso blocco; notti prima/durante/dopo, inclusa 01/06 e 30/09, partenze ai confini e soggiorni attraversanti; periodo da profilo coerente tra vista e anteprima; sole date/date incomplete salvate; profilo2027 assente e soggiorno2026→2027 bloccato; profilo2027 esclusivamente sintetico con diverso periodo 10/04–20/04 e confini/calcolo conformi. Regressioni: importi/cap/età/esenzioni, profili/categorie mancanti, token/CSRF/autorizzazioni/tenant, audit e snapshot storici conservati.

Fallimenti conservati: primo accesso VM negato dal sandbox, ripresa autorizzata; cache Python obsoleta del modulo guardia riportava6ac6 mentre sorgente/Git erano0dd17b4, rimossa soltanto la cache compilata VM e guardie invariate; suite inizialmente `RefreshDatabase` incontrava la migrazione storica `classificazioni.tipologia_struttura_id` (52 errori, nessuna prova fiscale eseguita), sostituito con `DatabaseTransactions` sullo schema effimero corrente previsto, senza cambiare migrazioni. Successivo run52/1539: due errori GD e una fixture2027 che aveva già risolto il controller con il servizio precedente; separata la prova sintetica in una nuova applicazione di test e dichiarati i due SKIP. Nessun difetto produttivo nuovo attribuito a questi errori del collaudo.

Evidenza locale privata: `output/correzione-periodo-imposta/test-isolati-ripresa.log`. Gli esiti non certificano l’intero modulo né nuove norme comunali.

## Git e rilascio

Diff applicativo limitato a servizio e vista; test pertinenti adattati al runtime corrente. Maestro aggiornato insieme alla correzione. Modifiche locali pregresse nei documenti e artefatti non pertinenti preservate ed escluse dallo staging; nessun reset/clean/force.

Produzione attestata prima del rilascio: HEAD8b7908f, Git pulito, precedente decisione CANDIDATO_ACCETTATO, barriera assente. Catena C5 esistente, nessun nuovo laboratorio o cambio infrastruttura. Rilascio/online ancora **PENDENTI**, da aggiornare soltanto con riscontri effettivi.

Revisione automatica: respinta una proposta di trasferire sigilli produttivi in un nuovo file locale per la verifica dello spazio; azione non eseguita. Alternativa: verifiche dentro la VM sulle copie già custodite, senza nuova esportazione di quel payload. Backup/chiavi/dati originali restano preservati.
