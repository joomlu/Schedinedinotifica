# Impatto dell’eventuale passaggio Laravel11→12 — 9 ottobre 2026

**Proposta di impatto, non implementazione.** Composer.json, lock e vendor del progetto restano su Laravel11.31.0. Simulazione in copia privata, script/plugin disabilitati, nessuna installazione o operazioneDB.

## Risultato concreto del solver

Obiettivo privato `laravel/framework:^12.69`, `phpunit/phpunit:^11.5.50`: soluzione12.69.3 con **50aggiornamenti e5installazioni,0rimozioni**. Il solver non segnala advisory nella soluzione simulata; non è prova di compatibilità dell’app né validazione del server. [Output completo](evidenze/herd-sicurezza-2026-10-09/laravel12-solver-ecosistema.log). I conflitti intermedi sono tutti conservati.

| Componente | Attuale | Soluzione simulata |
|---|---|---|
| Laravel | 11.31.0 | 12.69.3 |
| Sanctum | 4.0.3 | 4.3.3 |
| UI | 4.5.2 | 4.6.3 |
| Tinker | 2.10.0 | 2.11.1 |
| Sail | 1.38.0 | 1.68.0 |
| Collision | 8.5.0 | 8.9.5 |
| LaravelIgnition | 2.8.0 | 2.12.0 |
| PHPUnit | 10.5.38 | 11.5.57 |

Le altre modifiche sono prerequisiti framework/test: Symfony, Sebastian/PHPUnit, URI e diagnostica. Carbon3 è già presente. Aggiornare soltanto Laravel fallisce perché Sanctum e strumenti di sviluppo bloccati richiedono Illuminate11; non si può trattarlo come semplice sostituzione di un file lock.

## Impatto applicativo da valutare prima dell’implementazione

La [guida ufficiale](https://laravel.com/framework/docs/12.x/upgrade) richiede PHPUnit11 e Carbon3; segnala cambi per UUID, parametri opzionali nel container, validazioneSVG, merge di array annidati e default dello storage locale. Nel codice attuale non trovati usi HasUuids/HasVersion7Uuids, Concurrency::run o costruzione diretta DatabaseTokenRepository. Lo storage locale ha root esplicita `storage/app`, quindi non va sostituito automaticamente dal nuovo default.

Verifiche necessarie: avvio con bootstrap/kernel legacy e provider attuali; login/sessioni/impersonazione/reset/CSRF e middlewaretenant; resolveremail applicativo e regole di validazione; token/date/Rome/Cestino/concorrenza/rate limit; upload/avatar/loghi/immagini/SVG; export fiscali e storage cifrato ISTAT/Questura; merge nested dei componenti; discovery e compatibilitàPHPUnit11. Eseguire globale e Chromium sul nuovo runtime sintetico, con trap dei trasporti. Gli attuali750/8252 su Laravel11 non sostituiscono queste verifiche.

Servono anche compatibilitàPHP CLI/FPM, estensioni e requisiti install-no-dev sul destinatario. Non implica nuove migration applicative o modifiche operative automatiche. Backup/recuperabilità e gate provider restano separati.

**Decisione prima del cambio maggiore:** adottare questo percorso di migrazione supportato e verificarlo in isolamento, oppure mantenere temporaneamente il perimetro11 con condizioni dimostrate e gate di esposizione espliciti. Nessuna policy Composer disabilitata, nessun nuovo starterkit/installatore globale, nessuna stima temporale garantita. La presente task presenta l’impatto richiesto e non applica Laravel12.

## Decisione per il primo lancio — aggiornamento del 9 ottobre 2026

Framework attuale **11.31.0**. Sette righe advisory rappresentano sei segnalazioni distinte: tre debugXSS, una emailCRLF (duePKSA), una URLtemporanea firmata, una wildcardfile/imagearray. Condizioni e prove nominative nella [matrice Laravel](laravel-avvisi-residui-2026-10-09.json).

Verificato sul candidato: debugfalse nel runtime isolato; nessuna route signed/ValidateSignature né UserMustVerifyEmail; nessun wildcardfile nei consumer trovati e arrayavatar422; resolver applicativoCRLF2/16 e globale750/8252. Questi controlli non sono patch del framework, non attestano il debugFPM/cachedel destinatario e non coprono emaillegacy/ingressi che omettono la regolaemail. Non abilitare funzioni estranee al perimetro valutato.

La [policy ufficiale di supporto](https://laravel.com/framework/docs/12.x/releases) indica fine correzioni sicurezzaLaravel11 **12 marzo 2026**; Laravel12 riceve sicurezza fino al **24 febbraio 2027**. Alla data di questa valutazione11 è fuori supporto. Il precedente testo “mantenere temporaneamente il perimetro11” descrive soltanto un'alternativa condizionata al rischio, **non una certificazione di sicurezza del lancio pubblico**.

Non esiste nel lavoro verificato un aggiornamento ufficiale nella sola linea11 che rimuova tutti gli avvisi:11.36/11.44.1 coprono parte delle righe, mentre le altre patch indicate iniziano nella12. Il solver completo11 non ha già prodotto una soluzione senza advisory con policy invariata. Nessun bypass della policy Composer è approvato. Un fork/backport mantenuto richiederebbe analisi, verifica e gestione futura dedicate: non è oggi un'alternativa pronta o dimostrata.

**Scelta raccomandata per il primo lancio pubblico:** autorizzare la preparazione della migrazione12.69.x presentata, ancora senza deploy, e condizionarne l'adozione alla globale/browser sintetici e ai requisitiPHP del destinatario. Il dryrun12.69.3 con50update/5install senza advisory non basta a dichiararla accettata. Nessun major implementato ora.

Alternativa disponibile subito: mantenere11 esclusivamente per valutazione locale o pilota ad accesso ristretto, trasporti disabilitati e condizioni di esposizione attestate. Non presentarla come alternativa equivalente sicura al primo lancio pubblico; un'accettazione esplicita del rischio residuo non prolunga il supporto né risolve gli avvisi. Decisione pendente distinta dall'accettazione funzionale locale positiva.


## Implementazione successivamente autorizzata

Laravel12.69.3 implementato e verificato nel worktree separato: [risultati effettivi](LARAVEL12-VERIFICA-ISOLATA-2026-10-09.md). Globale750/8252,Chromium26/26,Composer0PASS; candidato318 e runtimeHerd11 preservati. La precedente simulazione resta storica. MinimoPHPeffettivo8.3, vincolo-platform8.3.0; Yaml6.4.40 nella linea originale. Non si confonde la prova locale convalidazioneproduttiva o adozione nel candidato principale.
