# Pubblicazione con backup su Extra — 18 ottobre 2026

**Candidato pubblicato e verificato online: `945ef553aab29ac437ff194f7cfad081fb7ab7c7`. Monitor conclusivo PASS. BASELINE IN AUDIT generale invariata.** Autorizzazione esplicita dell’utente: backup su Extra, recupero conservativo della capacità, VM da 32 a 36 GiB, pubblicazione C5, prove online con dati sintetici isolati e monitor completo. Nessuna trasmissione agli enti o email eseguita.

## Stato reale e ambito

All’inizio: produzione `dbee3028494eca70b99c5b77f06d07c95688689b`, candidato `945ef553aab29ac437ff194f7cfad081fb7ab7c7`, documentazione locale/GitHub `85a636c88fc8999d78b657d98df665467b59355d`. Identità rilette, produzione pulita e disponibile. SHA del candidato confrontati con i sorgenti delle prove precedenti e nuovamente collaudati. Nessuna modifica applicativa aggiunta in questa attività. Modifiche documentali e file non tracciati preesistenti preservati; il commit conclusivo contiene soltanto questo rapporto, le sue evidenze e l’aggiornamento circoscritto del Maestro.

[Evidenze selezionate](evidenze/pubblicazione-extra-2026-10-18/), con hash e ricevute; originali operativi in `output/pubblicazione-extra-2026-10-18`. Chiavi, password, SQL e log personali non sono inclusi nel commit.

## Extra, capacità e conservazione

Extra è realmente `/Volumes/extra`, APFS su USB PSSD T7, seriale `S6WYNS0W604273K`, UUID `488ACB8D-6B99-49AE-8A3C-175FD66F5466`, store osservato `disk4s2`. Verifica scrittura/rilettura di 4.096 byte PASS. Cartella esclusiva `/Volumes/extra/Schedinedinotifica-pubblicazione-20261018`; nessuna formattazione o cancellazione di contenuti precedenti. Contenitore condiviso con D, capacità conteggiata una sola volta. Liberi iniziali 739.798.581.248 byte, oltre i 40 GiB richiesti. La ricevuta di identità è stata aggiornata dopo il backup e riporta separatamente il libero di quel momento.

Conservati su Extra 237 cifrati C5 storici. SHA256 sorgente–copia–sorgente, HMAC prima della decifratura, decifratura integrale e digest del contenuto verificati per tutti: 183 tramite le corrispondenti copie nella VM e 54 leggendo direttamente Extra, esclusivamente in memoria nella VM. Prova effettiva di rientro, nuova doppia rilettura e assenza utilizzatori prima di ritirare le sole copie interne corrispondenti. Nessun database, SQL in chiaro, upload, chiave, credenziale o lavoro estraneo eliminato. Non si tratta di derivati semplicemente cancellati: sono archivi conservati e trasferiti dopo verifica di recuperabilità.

Mac: prima del primo trasferimento 1.368.227.840 byte reali liberi, dopo 10.763.956.224; incremento puntuale 9.395.728.384 byte. Le nuove copie C5 hanno poi consumato spazio: secondo trasferimento verificato 7.984.357.376 → 13.117.165.568 byte. Questi incrementi non sono il saldo finale e non includono capacità purgeable. Nessuna ulteriore pulizia indiscriminata.

## Backup cifrato e prova di recupero

Nuovo `VM-prima-ampliamento.sparsebundle` su Extra: APFS interno, capacità nominale 48 GiB, AES-256, conferma `hdiutil isencrypted` positiva. Chiave separata sul disco interno protetto da FileVault, directory 0700/file 0600: `/Users/jorgeluccitelli/.local/share/schedine-custodia-pubblicazione-20261018/recupero-vm.key`. Valore mai riportato nei log o su Extra. La disponibilità di questa custodia è necessaria al recupero; la sua perdita rende il backup inutilizzabile.

VM arrestata ordinatamente alle 08:53:57–59 italiane. Copia offline completa di 11 file: disco raw 32 GiB, EFI, configurazioni e identità dell’istanza e configurazioni/chiavi globali Lima. Hash sorgente–copia–sorgente, proprietà, permessi, date e attributi estesi PASS. Smontaggio, riapertura con la chiave e rilettura integrale di tutti i file PASS. Prova reale di avvio da copia recuperata di disco ed EFI sul contenitore cifrato: XFS, custodia della chiave e SELinux Enforcing PASS. Arresto ordinato, originali ripristinati e contenitore richiuso.

Fallimenti strumentali conservati: APFSX non supportato da quel comando hdiutil (nessuna immagine creata), API `os.listxattr` assente nel Python macOS (sostituita con `xattr` nativo, verifica completa ripetuta), rename EFI tra filesystem non ammesso nella prima chiusura della prova (originale preservato, copia verificata e intera prova ripetuta). Nessuna perdita di dati. Produzione disponibile durante tutte queste attività.

Per recuperare: verificare UUID Extra e cartella, aprire il contenitore con `hdiutil attach -stdinpass` alimentando la chiave tramite stdin dalla custodia, senza inserirla negli argomenti o stamparla; confrontare i digest con `backup-vm.json`. Ripristinare disco, EFI e configurazioni soltanto a VM ferma, preservando prima lo stato corrente; richiudere il contenitore dopo l’uso. Il backup rappresenta la VM prima della crescita, non le scritture produttive successive al rilascio.

Aggiunte su Extra anche nove copie cifrate della catena C5 corrente, 425.225.312 byte: PREPARE, S0 accettato e tentativo fermato. SHA256 sorgente–destinazione–sorgente PASS, copie interne conservate e chiave di trasporto non esportata. PREPARE e S0 accettato hanno già superato il recupero indipendente nella VM durante C5; la copia Extra aggiuntiva ha verifica SHA256, senza dichiarare un secondo restore separato.

## Crescita e collaudi isolati

Crescita mediante procedura Lima da 32 a 36 GiB, soltanto dopo almeno 8 GiB reali liberi sul Mac e backup/recupero PASS. Disco effettivo 38.654.705.664 byte, partizione 37.395.348.992; XFS 32.995.315.712 → 37.290.283.008 byte, incremento effettivo di 4 GiB. Liberi subito dopo 5.947.129.856 byte; SELinux Enforcing e servizi MariaDB/web interni attivi.

Nuovi collaudi sul candidato nella sola infrastruttura isolata:

- 192 test / 2.533 asserzioni e sei prove browser a 1440/768/390 px PASS;
- import 89 test / 353 asserzioni PASS, suite sovrapposte non sommate;
- 17 guardie, 23 override respinti, build e GEO PASS;
- sei prove mirate della diagnostica operativa C5 PASS;
- una deprecazione PHPUnit preesistente, invariata.

La discovery generica Deployment sul Mac ha mostrato soltanto l’help di un modulo e non è conteggiata. Nessun test Laravel eseguito sul Mac. Dopo il rilascio, VM locale allineata selettivamente a `945ef553`, con copia preventiva dei sorgenti sostituiti e preservazione di documenti, DB, env e upload. Ulteriori 17 guardie PASS nel namespace previsto. Una prima invocazione di queste guardie mancava del PATH Node: fallimento strumentale conservato, poi ripetizione completa positiva.

## C5: tentativo fermato e rilascio accettato

Preflight compatibilità PASS: Laravel 12.69.3, 156 migrazioni / 64 tabelle, schema invariato, zero DDL, provider Questura/ISTAT falsi e mail `array`, nove sonde HTTP/TLS/CSS. PREPARE completato fuori manutenzione, prebuild e recupero indipendente completo PASS. Gate di spazio VM superato, riserva di 1 GiB invariata. Prima del secondo RUN: liberi 3.562.659.840 contro residuo richiesto 2.403.196.928 byte; Mac 10.889.613.312 byte.

Primo piano `094f49c0e2d10e62a17b8a48487fce8118a7449e58da046e27d7f1c19ac7e2b0`, T0 09:18:17,144 italiane, nonce `89f8bebbe1bca8504ec68f088afd4af46ef11a35747f843e`, dopo 55 campioni quieti. STOP a 52,214 s durante trasporto: «Ingresso/gate non conforme durante operazione». Nessun execute, DDL o S0 accettato. Guardiano indipendente: BASELINE_NON_MODIFICATA e riapertura verificata a 55,302 s. BrokenPipe successivo del client è conseguenza della chiusura del worker. Stato, cifrati, errori e lock originale preservati; guardiano concluso inattivo e disabilitato.

Causa puntuale del primo STOP non ricostruibile: SourceBackend non conservava `monitor.reason` durante acquisizione/trasporto. Il socket precedente era condiviso con altri siti, ma ciò non dimostra da solo la causa; nessun processo estraneo terminato. Correzione soltanto operativa in `monitor.py`/`backend.py`: registrazione privata della causa dopo SIGUSR1, errore di logging incapace di impedire STOP. Diff normalizzato con controlli originari identici e sei prove isolate PASS. Nessun gate o soglia abbassato.

Nuovo kit `34cf48ad0086c389d9e73716628dc4ae79ac96c15ab4d44658500bdd508d1ea3`; nuovo piano `c2f375a6e85ba8b856712537f6b25113f8b55ba5f7602c6dbdc1d33d1afd5e3f`. Prima della ripresa: baseline pulita, DB byte-identico al primo snapshot, PREPARE/prebuild/schema/immutabili riconfermati, verificatore cold invariato. Nessun riuso del primo T0/S0 fallito. Budget 1.800 s, S0 entro 600 s, cutoff 1.379 s e riserva 421 s immutati.

Secondo T0 09:28:16,149850 italiane, nonce `25fcd1a22566e4377e6cdfe4935dc64f763f813035bdbef7`, dopo altri 55 campioni quieti. **S0 indipendente PASS a 146,299 s; candidato accettato e riaperto a 226,157 s**, 397 campioni interni, zero DDL. Guardiano indipendente concluso inattivo/disabilitato. Prima delle prove sintetiche: DB byte-identico a S0, 75 persistenti/env/APP_KEY/upload/logo preservati, sorgenti attesi conformi, Git produttivo pulito, nove sonde PASS. Delta FPM SHA256 `2004732c799122f7a1c5d2e1a32fdabab89963007a953c19a3cf2d32903255a0` e logo Rimini conservati.

Rollback coordinato preparato e verificato tramite recupero indipendente: DB, applicazione, dipendenze, build, file e metadati. Il primo STOP ha dimostrato la riapertura della baseline non modificata; non è stato necessario eseguire un restore produttivo dopo DDL. Dopo riapertura e nuove scritture non va applicato automaticamente uno S0 obsoleto con perdita dei dati successivi.

## Accettazione effettiva online

Due account temporanei con solo ruolo `struttura_user`, limitati alle strutture sintetiche A/B. Nessun account reale modificato. Prove nel browser sul candidato realmente online:

- ricerca Nome e Cognome, tre soli risultati A e cliente B escluso; selezione esplicita Ada;
- bozza incompleta 87 salvata e riaperta con persistenza;
- ingresso da Clienti tramite «Crea schedina», soggiorno 10–12 novembre 2026, schedina definitiva 88 salvata e riletta con due letti;
- famiglia con due persone, creazione componente e successivo aggiornamento singolo persistente;
- Nuova / Elenco / Bozze a 1440, 768 e 390 px, senza overflow orizzontale esterno;
- utente B vede soltanto EsclusivaB; accesso al cliente A e alla schedina A respinto con 404.

Il comando «Salva componente» su componente ancora privo di ID richiede prima il salvataggio dell’intera schedina: la validazione ha respinto il tentativo prematuro. La creazione è poi riuscita tramite il normale salvataggio complessivo e l’aggiornamento singolo su ID esistente è PASS. Nessun ID forzato o protezione modificata. L’input di ricerca richiede eventi attendibili: completata la prova nel browser integrato con pressione reale dei tasti, mantenendo `event.isTrusted`.

Fixture, componenti, due schedine, account temporanei e strutture A/B rimossi. Confronto HMAC di **tutte le righe preesistenti**: nessuna tabella differente, nessun restore DB. I contatori AUTO_INCREMENT sono avanzati e non sono stati azzerati. Sessioni disconnesse e privilegi effimeri revocati con eliminazione degli account. Nessun invio reale.

## Monitor e chiusura

Primo monitor: 918,345 s, 31 campioni e 279 sonde HTTP/TLS/CSS PASS; controlli FPM, chiave e provider PASS. Audit log dal T0: tre errori del preparatore CLI delle fixture, tutti attribuiti con stack al file operativo e digest del blocco: uno per cataloghi, due per `telefono` obbligatorio. Tentativi falliti prima degli inserimenti o con rollback transazionale; corretti soltanto i dati del preparatore. Nessun log cancellato. Non si dichiara zero errori sull’intero ciclo. L’audit copre gli errori registrati da Laravel e journal FPM; la configurazione PHP esistente con logging disattivato non consente di attestare eventi mai registrati.

Dopo pulizia delle fixture, **nuovo monitor completo PASS: 918.242 s, 31 campioni, 279 sonde, zero nuovi errori Laravel e zero errori journal FPM**. Finestra distinta da quella del primo monitor, stesso T0 C5 originale conservato e stesse soglie. [Ricevuta conclusiva](evidenze/pubblicazione-extra-2026-10-18/ricevuta-finale.json) e [stato online finale](evidenze/pubblicazione-extra-2026-10-18/stato-finale.json): HEAD945ef553, Git pulito, sorgenti/protetti conformi, FPM attivo, barriere assenti, entrambi i guardiani inattivi/disabilitati e nove sonde pubbliche PASS.

Spazio Mac oscillante durante la chiusura: una prima asserzione aggiuntiva finale non superata; misura successiva 8.613.421.056 byte. Non è stato forzato alcun controllo. Successivamente conservati tutti i 18 duplicati cifrati della sola operazione tramite copie APFS CoW: SHA256, metadati, attributi e assenza utilizzatori verificati; tutti i percorsi e gli archivi restano presenti. Recupero puntuale 12.447.883.264 → 13.306.134.528 byte, pari a 858.251.264 byte. La variazione precedente del libero non viene attribuita a questa operazione né conteggiata come recupero eseguito.

Ultima capacità effettiva: **Mac13.305.942.016 byte, VM2.501.234.688 byte, Extra690.396.528.640 byte**; server168839196672 byte. DiscoVM36GiB, XFS37.290.283.008 byte, SELinuxEnforcing, servizi locali attivi; UUIDExtra riconfermato, immagine backup chiusa e custodia0700/0600. Il residuo VM richiederà un nuovo preflight prima di qualsiasi futuro aggiornamento; non si dichiara già superato un futuro C5.

Chiusura applicativa, prove online e monitor completati. Aggiornamento GitHub documentale distinto dal candidato online; SHA del commit conclusivo nella ricevuta Git esterna in output, evitando riferimenti circolari nel commit stesso.

Pendenti generali: BASELINE IN AUDIT non equivale a certificazione complessiva del prodotto; deprecazione PHPUnit preesistente e causa puntuale del primo STOP non recuperabile. Restano esclusi invii agli enti, email e validazioni normative non richiesti. Nessun pendente funzionale osservato nei flussi online elencati; monitor conclusivo completato PASS.
