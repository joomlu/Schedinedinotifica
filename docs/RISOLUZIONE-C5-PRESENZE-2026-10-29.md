# Risoluzione C5 e pubblicazione correttivo Presenze — 29 ottobre 2026

## Stato corrente

Correttivo PR46 `455318e7dc477b47402313826e1525fc691589c3` effettivamente online, C5 PASS. Merge applicativo GitHub `328203d41cc1f05c29449a21423f9503247c9e38`, albero identico al candidato qualificato. Monitor tecnico conclusivo PASS: 31 campioni su 917,741 secondi e 279 sonde; QA applicativa PARZIALE per il residuo URL descritto sotto. Il rilascio serve esattamente PR46; un ulteriore difettoURL emerso dalla QA è corretto soltanto localmente e descritto sotto. BASELINE IN AUDIT generale invariata.

## Mandato e preservazione

Autorizzazione del 29 ottobre: risolvere C5 e pubblicare soltanto con controlli conformi; VM schedine-sviluppo-interno ferma a 56 GiB, nessuna nuova copia integrale della VM, riserva 1 GiB e recupero 240 secondi invariati. Pulizia autorizzata esclusivamente sui dodici datadir dell’analisi del 28 ottobre. Repository originale main/a6d40647, indice e lavori preesistenti preservati; lavoro documentale nel clone separato. Produzione riletta prima dell’intervento: c560979f, Git pulito, 328 sorgenti/protetti e nove sonde conformi.

Tutti i dodici percorsi esatti sono stati ricontrollati: directory fisiche senza symlink, SQL/ridump identici e corrispondenti al sigillo, SHA256 e HMAC di DB/FS/inventario cifrati, chiave presente e decifratura DB in memoria identica allo SQL. Nessun processo, file aperto, mappa o riferimento nei servizi systemd verificati; controllo di inutilizzo ripetuto immediatamente prima del ritiro. Tutti sicuri, nessun candidato scartato. Rimossi soltanto i dodici dbdata, non le directory madri. SQL, ridump, cifrati, chiavi, inventari, log e diagnostici conservati. Le nuove prove conservano i rispettivi datadir: nessuna estensione della pulizia ad altri percorsi.

| Misura | Byte | GiB circa |
|---|---:|---:|
| Liberi prima della pulizia | 3.463.286.784 | 3,225 |
| Liberi dopo la pulizia | 5.956.538.368 | 5,547 |
| Recupero reale fra le due letture | 2.493.251.584 | 2,322 |
| Liberi prima del nuovo gate C5, dopo prova completa | 4.669.403.136 | 4,349 |
| Gate richiesto, compresa riserva | 4.014.243.840 | 3,739 |
| Minimo libero campionato nel nuovo circuito C5 | 2.017.611.776 | 1,879 |
| Picco aggiuntivo campionato nel nuovo circuito | 2.651.852.800 | 2,470 |

Campionamento ogni 0,5 secondi; i massimi/minimi sono campionati, non una misura atomica di ogni allocazione. XFS rilascia parte dei blocchi in differita: i delta immediati dei singoli ritiri non vanno sommati come recupero definitivo. Nessun trim, deduplicazione, ampliamento o nuova immagine VM. Il recupero guest non equivale a uguale diminuzione immediata dello sparsebundle su Extra.

## Diagnosi misurata e recupero completo

Il vecchio RUN fu interrotto nella chiamata cold_verify dopo 240 secondi; il report sorgente arrivò a 261,413 secondi e il minimo libero campionato scese sotto 1 GiB. La vecchia stima sottovalutava il picco: questo difetto di dimensionamento è accertato. Mancavano però cronometri delle singole fasi, quindi non è dimostrata una causa esclusiva di tutti i secondi di ritardo.

Nuova prova sul medesimo S0 conservato, con verificatore originale e namespace rete/PID/mount, SELinux Enforcing, tutte le verifiche attive, limite esterno 240 secondi: recupero completo **170,700 secondi PASS**. Strumentazione diagnostica, non una ricevuta fresca di rilascio. Nessun controllo rimosso o contenuto ridotto.

| Fase osservata | Secondi circa |
|---|---:|
| Decifratura del delta DB/FS/inventario | 0,829 |
| Prima riconciliazione etichette SELinux | 5,405 |
| Decifratura archivio e inventario base | 18,794 |
| Ricomposizione archivio completo base+delta | 48,246 |
| Estrazione e ripristino metadati, incluse verifiche etichette | 44,759 |
| Verifica integrale file/metadati estratti | 21,464 |
| Verifica archivio combinato, gestione intermedi e inizializzazione DB | circa 24,0, residuo temporale fra le fasi da cronometrare separatamente |
| Importazione DB e ridump byte-identico | 3,638 |
| Prove finali FPM, chiave, TLS e chiusura processi | incluse nel totale |

Le fasi annidate non si sommano due volte. Il costo dominante misurato è la materializzazione/verifica completa della base, non il piccolo delta di rete né il solo import SQL. Picco campionato della prova completa 2.584.834.048 byte, minimo libero 3.371.540.480, tutti i controlli DB/file/metadati/chiave/FPM/TLS/flags/isolamento positivi. Il rilascio è stato qualificato senza alterare il kit sigillato: la correzione tecnica necessaria è stata recuperare il margine autorizzato e sostituire la stima incompleta con il picco di una prova terminata, più cifrati nuovi, 256 MiB per crescita, 32 MiB di margine e riserva 1 GiB. Il timeout storico resta conservato, non riclassificato come PASS.

## Nuovo C5 e conservazione

Base PREPARE indipendente già positiva e custodita: prima del riuso ricontrollati integralmente cifrati/sigilli, contenuti immutabili correnti, schema e prebuild. Nessuna base differente o ricevuta vecchia presentata come nuovo S0. Nuovo piano e approvazione vincolati al mandato, storico dello STOP conservato sotto il lock originale, nuovo T0/nonce/S0, 55 campioni di quiete positivi. Kit SHA256 `d936053f1210c3c97a71cdffa4f29760e4a19c0913931fa399a737abf296d460` invariato. Unico delta del piano: metadato di ripresa; nessuna guardia allentata.

RUN PASS, candidato accettato: **S0 completo 161,731 secondi**, **RUN 265,062 secondi**, 438 campioni interni, zero DDL. Il tempo S0 include acquisizione, trasferimento, recupero indipendente e verifica della ricevuta: non è il solo cronometro del cold_verify. Il cold_verify è terminato entro il suo timeout invariato di 240 secondi; il circuito S0 resta entro 600 secondi. Finestra1800/cutoff1379/riserva421 e trasferimento120 invariati.

Git produttivo pulito sul candidato 455318e7; DB byte-identico al nuovo S0 prima della QA, schema66tabelle/161migrazioni, 104 persistenti conformi, chiavi/provider/FPM e nove sonde positivi. Custodia Extra del nuovo S0: tre cifrati, 30.262.640 byte, integrità/autenticazione/decifratura positiva, chiave separata nella custodia VM esistente. Guardiano disarmato, concluso, inattivo e disabilitato. Tutte le fonti e i diagnostici negativi restano conservati.

## Verifica online

Sessione Chrome reale già autenticata, nessun nuovo utente o sessione artificiale. Prima apertura403 con selezione struttura assente; la scheda utente precedente mostrava Hotel Amalfi. Ripristinata soltanto tale selezione mediante il selettore standard: azione di sessione, non modifica a prenotazioni/anagrafiche. Successive verifiche Presenze e stampe con soli GET.

Le cinque schede, i cambi diretti e le stampe sono positivi; la prova aggiuntiva di ricarica descritta sotto è invece negativa. Selezione giugno2026 in Occupazione: periodo01/06–30/06, 1800camere-notte/2700posti-notte. Passaggio a Dettaglio: stesse date; Riepilogo: giugno–giugno; Situazione:01/06; Arrivi/Partenze: stesso periodo. Entrambe le stampe riportano giugno correttamente. Periodo reale vuoto: lo zero non viene usato come prova delle formule; R10 isolato45PHP/213asserzioni e3browser resta la qualifica applicativa precedente sul medesimo commit. Nessuna suite nuova o globale attestata. Persistenti ricontrollati dopo la QA e invariati.

## Residuo scoperto dalla ricarica

Dopo Occupazione giugno → Dettaglio, i valori visibili e la stampa sono corretti ma l’indirizzo mantiene dal/al gennaio–dicembre; ricaricando Dettaglio il server rilegge quel vecchio intervallo. Riprodotto online senza dati sintetici. La QA complessiva è quindi PARZIALE, non PASS. Nuova correzione locale, qualificata ma NON online: indirizzo normalizzato dai filtri effettivamente applicati dal server, sia al caricamento sia al cambio scheda. Aggiunte prove di ricarica Dettaglio/Riepilogo dopo febbraio2024 bisestile. R11 isolato riproduce il difetto sul codicePR46 in tutti e tre gli scenari1440/768/390, con fallimento mirato sull’URL; build/GEO e23rifiuti override positivi. R12 sulla correzione `911f86ecc80409e4ba457eec1906ce321213eec0` PASS:45PHP/213asserzioni e3browser1440/768/390, ricarica di Dettaglio e Riepilogo dopo febbraio2024, build/GEO e23rifiuti override. Sorgenti dei due file confrontati con la qualifica; nessuna modifica successiva. Nessun nuovo rilascio di questa correzione senza collaudo e nuovo C5.

## Gate dell’ulteriore pubblicazione URL

Dopo i recuperi conservati e il ripristino verificato del clone applicativo sono liberi **3.125.018.624 byte** (circa2,91GiB). La versione online455 differisce negli immutabili dalla vecchia basePREPAREc560: il nuovo candidatoURL richiede una nuova base, non il riuso della vecchia ignorando le differenze.

Budget preventivo della sequenza: nuova base completa residente1.945.706.496byte + picco completoS0misurato2.651.852.800 (già comprensivo del nuovo ingresso, senza duplicarlo) + riserva1.073.741.824 + margine32MiB + crescita256MiB = **5.973.291.008byte** (circa5,56GiB). Deficit **2.848.272.384byte**, circa2,65GiB. È un budget prudenziale fondato su prove concluse, non un minimo fisico assoluto.

STOP soltanto al nuovo rilascioURL: nessun nuovoPREPARE/RUN o copia inutile iniziati. PR46resta operativa455318e7; il correttivo URL è salvato nella [PR48 in bozza](https://github.com/joomlu/Schedinedinotifica/pull/48), senza integrazione o deploy. Per proseguire nei56GiB occorre rendere disponibili almeno2,65GiB aggiuntivi rispetto al budget corrente, mediante un nuovo inventario/ritiro esplicitamente autorizzato di ricostruzioniC5 rigenerabili, con verifica delle fonti e conservazione diSQL/cifrati/chiavi/diagnostici. I dodici percorsi autorizzati sono esauriti; non vengono estesi ad altri materiali, né viene riproposto64GiB.

## Chiusura e limiti

Monitor finale positivo: 31 campioni su 917,741 secondi, 279 sonde HTTP/TLS/CSS, zero nuovi errori applicativi e FPM dall’intero RUN. Verifica conclusiva: 455318e7 ancora online, Git pulito e database byte-identico al nuovo S0 anche dopo QA e monitor. Ricevuta tecnica C5 positiva; nessuna dichiarazione di QA applicativa completa. Consuntivo salvato su GitHub separatamente dalla PR48 in bozza. Nessun altro rilascio applicativo necessario per pubblicare il solo consuntivo. Le precedenti proposte64GiB sono ritirate e non eseguite. Eventuali limiti dei dati/modello e BASELINE IN AUDIT generale restano quelli del rapporto Presenze.

[Evidenze sanificate](evidenze/risoluzione-c5-presenze-2026-10-29/pulizia.json), [profilo](evidenze/risoluzione-c5-presenze-2026-10-29/recupero-profilato.json), [gate capienza](evidenze/risoluzione-c5-presenze-2026-10-29/gate-capienza.json), [QA online](evidenze/risoluzione-c5-presenze-2026-10-29/qa-online.json).
