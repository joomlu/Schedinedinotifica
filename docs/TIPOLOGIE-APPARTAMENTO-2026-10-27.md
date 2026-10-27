# Tipologie ricettive e appartamento individuale Questura — 27 ottobre 2026

## Ripresa autorizzata e revisione PR38

L’utente ha risposto «autorizo tutto» alla richiesta specifica di push pubblico/PR e ampliamento VM 44→48 GiB con backup integrale cifrato autenticato su Extra, chiave separata sul Mac e decifratura integrale. Il push successivo è riuscito; il precedente rifiuto automatico resta evidenza storica. [PR38](https://github.com/joomlu/Schedinedinotifica/pull/38) aperta inizialmente in bozza e resa pronta alla revisione automatica sul candidato `7372076d`.

La revisione ha individuato un P2 riproducibile nel percorso installazione nuova: CAV viene creata dal seeder dopo le migrazioni, saltando le associazioni ai soli. Regressione dedicata aggiunta; correzione e nuova qualifica da completare prima del rilascio. Nessun merge o deploy anticipato.

Preflight risorse PASS: daily `945ef553`, DB logico 3.332.249 byte, 84 file protetti e SELinux Enforcing sigillati; XFS 45.880.217.600 byte, 2.782.883.840 liberi. Mac 13.484.601.344 ed Extra 507.460.022.272 byte liberi. Arresto ordinato concluso, backup integrale in corso; disco ancora 44 GiB. Prova del formato AES-GCM PASS, alterazione e due troncamenti respinti. Usato il Python fornito dall’app con cryptography già presente, senza installazioni.

Kit C5 preparato soltanto privatamente: due migrazioni nominate, nessuna nuova tabella, guardie e budget invariati. Verificatore del delta DB provato su sintetici: tre colonne nullable, sole aggiunte catalogo/ledger, quattro mutazioni respinte. QA online predisposta su due strutture sintetiche, non eseguita. PREPARE/S0/RUN/DDL/QA/monitor pendenti. Rilettura produttiva: `ac7dd4a9`, Git pulito, 159 migrazioni/66 tabelle, barriera assente, Questura/ISTAT OFF. Nessuna trasmissione reale.

**Stato corrente: modifiche PUBBLICATE su79b5d7e7. R12 395test/5.429asserzioni e sei browser PASS; C5 e QA online PASS. Monitor finale in corso dopo pulizia, nessun PASS anticipato. Questura/ISTAT OFF, nessuna trasmissione reale. BASELINE IN AUDIT invariata.**

## Perimetro e stato iniziale

Mandato: indagare il selettore esistente, completare il profilo individuale Gestione Appartamenti e verificarne gli effetti, preservando ISTAT, Tassa, Schedine e Web Check-in. Raggruppamenti File Unico, invii centralizzati e rifacimenti esclusi.

Checkout originale `main/a6d406470aa63d7ff69422efcc0b1c14033e0ee5` con modifiche preesistenti, inclusi quattro sorgenti Questura già sincronizzati dopo il rilascio precedente. Attestati privatamente 8.700 file; nessun reset o pulizia. Nuovo worktree separato dalla base GitHub disponibile `4a831f80a2d9df846b1008aa952b7340f3b6ec5d`, comprendente PR36 e consuntivo successivo. Produzione letta senza mutazioni: `ac7dd4a9c88a6e979f073a60fe8b715523f9821e`, Git pulito, FPM attivo, barriera assente, sette strutture attive, nessuna terna completa di credenziali, zero export/trasmissioni/ricevute/riserve Questura. Nessun dato ospite o segreto estratto.

Letti Maestro, AGENTS, isolamento e consuntivi Questura del 27 ottobre, ISTAT del 26 e Tassa del 24. I relativi PASS sono storici e circoscritti: non costituiscono una nuova qualifica di questo candidato.

## Matrice iniziale e intervento minimo

| Punto | Stato ed evidenza iniziale | Problema dimostrato | Correttivo circoscritto |
| --- | --- | --- | --- |
| Agriturismo, affittacamere, ostello, locazione imprenditoriale | Presenti nel DB produttivo e nel catalogo, sotto Extra-alberghiera | R1: Agriturismo resta disabilitato dopo cambio categoria nelle tre larghezze | Eventi jQuery di Select2 ricevuti dalla cascata; stato opzioni riletto nella ricerca |
| Locazione non imprenditoriale | Assente nel catalogo e nel DB letto | Opzione inesistente | Migrazione additiva mirata, senza seeder generale |
| Classificazione CAV in ER | Solo Nessuna nel catalogo letto | Mancano i soli previsti dalla fonte regionale | Aggiunta relazioni 2–4 soli, conservando ogni relazione precedente |
| Locazione non imprenditoriale in ER | Tipologia assente | Manca anche la classificazione facoltativa | Nuova opzione con Nessuna e 2–3 soli; nessuna selezione automatica |
| Profilo e ID Questura | Campi assenti; trasporto usa Test/Send standard | Nessuna associazione individuale possibile | Due colonne nullable, form e validazione negli stessi permessi operativi |
| Appartamento individuale | Generatore standard e infrastruttura idempotente già presenti | Mancano metodi individuali e contesto del destinatario | Riutilizzo TXT 168; metodi con ID separato; contesto vincolato Test/Send |
| ISTAT/Ross1000 | Codice assegnato e configurazione indipendenti | Nessun errore di corrispondenza riprodotto | Nessuna modifica al modulo |
| Tassa Bellaria | Calcolo certificato nel codice per alberghi; altre categorie bloccate esplicitamente | Non è dimostrato un errore causato dal selettore | Preservare blocco e regole; completamenti fiscali fuori scope |

R1 usa utenti sintetici, il vero menu Select2 e le tre larghezze 1440/768/390. Non usa `selectOption` per aggirare il problema. R3 dimostra che il solo aggiornamento della cache non basta: manca anche la ricezione del change jQuery da parte degli ascoltatori DOM nativi. Entrambi i correttivi sono limitati ai selettori della cascata.

## Fonti e distinzione dei sistemi

Consultazione **27 ottobre 2026**. Le classificazioni seguenti riguardano l’Emilia-Romagna; altre regioni richiedono propria verifica. Le opzioni nazionali/legacy non vengono cancellate né presentate come automaticamente valide in ogni territorio.

- [Disciplina regionale delle strutture ricettive](https://imprese.regione.emilia-romagna.it/turismo/ambiti/alberghi/disciplina-delle-strutture-ricettive-dirette-allospitalita): L.R.16/2004 e direttive richiamate. Tabella sintetica sotto; le specificazioni commerciali non sono automaticamente categorie autonome.
- [Agriturismi, classificazione ufficiale ER](https://agricoltura.regione.emilia-romagna.it/agriturismo-e-multifunzionalita/agriturismo/classificazione-degli-agriturismi): DGR1185/2015, uno–cinque girasoli per ospitalità in camere, appartamenti o agricampeggio. Il precedente simbolo regionale era la margherita: le spighe già nel catalogo sono conservate come legacy, senza certificarle per ER. [Disciplina dell’operatore](https://agricoltura.regione.emilia-romagna.it/agriturismo-e-multifunzionalita/agriturismo/come-diventare-operatore-agrituristico): attività agricola e abilitazione proprie, nessuna assimilazione automatica ad albergo o appartamento Questura.
- [FAQ regionali sulle locazioni](https://imprese.regione.emilia-romagna.it/semplificazione-e-sportello-unico/schede-attivita/faq-circolari-normativa/faq/strutture-ricettive): fattispecie ER artt.11 CAV imprenditoriale e 12 appartamento ammobiliato non imprenditoriale. La voce generica nazionale «Locazione turistica imprenditoriale» è conservata; in ER va verificata la posizione autorizzata, senza rinomina automatica.
- [Rilevazione ISTAT capacità](https://indata.istat.it/ctur/index.php) e [definizione statistica regionale](https://statistica.regione.emilia-romagna.it/dati/fb/amb/off_tur): aggregazioni alberghiere ed extra-alberghiere distinte dalle classi amministrative; agriturismi e ostelli hanno proprie voci statistiche. La pagina ISTAT è reperibile nella ricerca, ma l’apertura diretta ha restituito 502: nessun codice numerico aggiornato dedotto da documenti storici.
- [Ross1000, pagina ufficiale](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo), [tracciato XML-WS2.4](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/tracciatoxml-webservice.pdf/@@download/file): codice struttura assegnato dall’ente, prodotto e movimenti giornalieri; disponibilità espresse in unità ricettive e posti letto. Tipi alloggiato e codici geografici seguono tabelle Polizia; non è definita una conversione del nostro ID tipologia in un codice universale. La posizione anagrafica Ross1000 deriva dalla pratica comunicata dagli enti locali.
- [Comune Bellaria, ufficio IDS](https://www.comune.bellaria-igea-marina.rn.it/it/unita_organizzative/imposta-di-soggiorno) e [regolamento pubblicato](https://www.comune.bellaria-igea-marina.rn.it/it/documenti_pubblici/regolamento-imposta-di-soggiorno-ids): categoria e tariffa dipendono da atti comunali e periodo. La pagina richiama CC71/2024, CC22/2025 e GC185/2024 per il 2025. Aperture dirette dello sportello e di un allegato tariffario hanno restituito rispettivamente 502/403: nessuna estensione fiscale al 2026 o alle categorie non certificate ricavata da tali insuccessi. Restano validi i profili versionati già verificati nel progetto, senza nuovi automatismi.
- Il [PDF del regolamento comunale accessibile](https://municipium-images-production.s3-eu-west-1.amazonaws.com/s3/602/allegati/amministrazione/documenti-e-dati/atto-normativo/regolamento_ids_2025.pdf), art.2, include espressamente agriturismi, affittacamere, B&B, CAV, appartamenti e locazioni brevi, oltre alle altre strutture elencate. Prevede giugno–settembre, sei pernottamenti, dieci consecutivi nei campeggi e dieci nel mese nelle aree camper. L’art.4 rimette aliquote e articolazione alla Giunta: l’inclusione nel presupposto non certifica una tariffa o una codifica StayTour. Nessuna regola di calcolo è stata cambiata.
- [Manuale Alloggiati Web Service](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/Manuali/MANUALEWS.pdf), rev.01/24-01-2022, pp.11–14,18–20, e [WSDL ufficiale](https://alloggiatiweb.poliziadistato.it/service/service.asmx?WSDL). Nuovo download TLS del WSDL identico allo snapshot: SHA256 `36d5279a0577f0b116ac74f6664246532685bc0178b1d6c6a1f7926c11235cab`. Profilo effettivo dell’utenza separato dalla tipologia. Metodi individuali `GestioneAppartamenti_Test/Send` con `IdAppartamento` di tipo `xsd:int`; ID attivo appartenente all’utenza. L’esempio della tabella include `0`; gli esempi SOAP includono zeri iniziali. Si conserva il valore numerico. File Unico distinto, con sei caratteri aggiunti al record: non implementato.

## Matrice delle tipologie

| Voce del catalogo | Inquadramento ER verificato o limite | Posizione statistica da verificare sull’anagrafica dell’ente |
| --- | --- | --- |
| Hotel / albergo | Alberghiera, classi 1–5 stelle e specificazioni previste | Alberghiera, classe assegnata |
| RTA | Alberghiera, 2–4 stelle | RTA |
| Agriturismo | Disciplina agricola L.R.4/2009; 1–5 girasoli | Alloggi agro-turistici |
| Affittacamere | Extralberghiera | Posizione assegnata, nessun codice numerico inventato |
| Bed & Breakfast | Attività saltuaria, altra tipologia | B&B |
| CAV | Extralberghiera imprenditoriale, 2–4 soli | Alloggi in affitto secondo registrazione |
| Locazione turistica imprenditoriale | Verificare la fattispecie CAV ER sulla pratica | Registrazione Ross1000 da confermare |
| Locazione turistica non imprenditoriale | Appartamento ammobiliato uso turistico; 2–3 soli facoltativi | Registrazione Ross1000 da confermare |
| Ostello | Extralberghiera | Ostelli |
| Casa per ferie | Extralberghiera | Case per ferie |
| Rifugio alpino / escursionistico | Extralberghiera | Voce rifugi secondo registrazione |
| Campeggio | Aria aperta, 1–4 stelle ER | Campeggi |
| Villaggio turistico | Aria aperta, 2–4 stelle ER | Villaggi turistici |
| Camping village | Verificare titolo e classificazione della forma mista | Forma mista campeggio/villaggio |
| Area sosta camper | Altra tipologia, titolo comunale da verificare | Registrazione da confermare |
| Marina resort | Aria aperta; classificazione specifica da verificare | Registrazione da confermare |
| Albergo diffuso / villaggio-albergo / motel / meublé-garni | Specificazioni alberghiere; titolo e classe della pratica | Non attribuire un nuovo codice dal solo nome |
| Condohotel / resort | Catalogo nazionale conservato; specifica posizione regionale non attestata in questa ricerca | Conferma dell’ente necessaria |

Per **ogni riga**, Alloggiati richiede l’utenza realmente abilitata: standard oppure Gestione Appartamenti con l’ID dell’elenco attivo. Agriturismo o affittacamere non impongono uno dei due profili. Non risulta nei contratti consultati un codice tipologia comune a Questura, Ross1000 e Tassa. Per **ogni riga** la categoria fiscale Bellaria e l’identificativo comunale vanno verificati separatamente: il modulo attuale certifica alberghi, non queste altre voci. Nessuna tariffa/codice esterno/CIR/CIN è riscritto dalla scelta nel catalogo.

## Implementazione e conservazione

Il campo è denominato **ID appartamento Alloggiati Web**, con spiegazione dell’elenco attivo, nel form operativo esistente; il collegamento Configurazioni → Questura / Alloggiati Web porta allo stesso form. Il profilo Standard resta il default anche per le righe precedenti con colonna nulla; ID facoltativo e trasporto Test/Send invariato. Gestione Appartamenti richiede l’ID: politica locale di indice non negativo entro il massimo `xsd:int`, zero compreso; nessuna assegnazione o convalida remota dell’appartenenza simulata.

`QuesturaProfilo` centralizza il contesto; `AnagraficaStruttura` conserva gli stessi permessi operativi, estendendo l’allowlist ai soli due campi nuovi. Password/WSKEY restano cifrati e non ripopolati. Due colonne nullable additive su struttura e un metadato JSON nullable sullo storico trasmissioni: nessun aggiornamento dei sette record esistenti. La migrazione catalogo aggiunge soltanto tipologia/soli/pivot mancanti, rifiuta ambiguità anziché deduplicare, conserva ID, date, stringhe e relazioni precedenti. Il suo down non cancella voci potenzialmente referenziate: recupero produttivo da S0 coerente e verificato, mai rollback dati alla cieca.

Il generatore TXT non cambia. Anteprima, download, UTF-8, gruppi/famiglie e archivio mantengono 168 caratteri per riga. Il servizio seleziona il metodo individuale e normalizza la relativa chiave di risposta. Il contesto contiene profilo, ID e impronta dell’utenza; Send richiede il medesimo contesto del Test positivo. Cambiare ID, utenza o profilo impone un nuovo Test. Le riserve per identità della schedina, esiti parziali/incerti, timeout e minimizzazione restano nel circuito esistente. Il solo payload non bastava allo storico: la minimizzazione lo azzera esplicitamente. Il metadato separato, nascosto dalla serializzazione generica e protetto dallo stesso vincolo di immutabilità, conserva l’ID dello snapshot anche dopo ricevuta, senza conservare gli ospiti nel payload. Il servizio di retention non è modificato. Nessun invio reale né accettazione esterna attestati; guardrail OFF preservati.

## Verifiche e stato della consegna

- R1-BASE: tre browser NONPASS attesi, identico blocco Agriturismo dopo cambio categoria, prima dei correttivi.
- R2: 37 test/304 asserzioni, un fallimento della nuova fixture che riutilizzava il contesto corrente in un ciclo; browser non eseguiti. Diagnostico conservato.
- R3: 39 test/313 asserzioni PASS dopo separazione degli scenari; tre browser NONPASS perché la prima variante correggeva solo la cache e non gli eventi jQuery. Nessun PASS globale.
- R4-BROWSER: tre browser PASS sul selettore. Ispezione visiva successiva: etichetta classificazione non riallineata dopo reset; aggiunta la notifica al componente.
- R5: 345 test/3.912 asserzioni PASS; tre browser tipologie, inclusi salvataggio/rilettura, PASS. Tre browser configurazione NONPASS su un’asserzione del collegamento cercato come visibile nel menu chiuso. Nessun PASS complessivo.
- R6: 42 test/384 asserzioni e sei browser PASS. Compresa serializzazione SOAP in memoria dal WSDL, ID0/4 e righe separate dal parametro. Per Agriturismo la fixture browser finale è stata precisata con tre girasoli, anziché la generica classe sintetica Nessuna.
- R7-FINALE: 390 test/5.409 asserzioni e sei browser funzionali PASS sul candidato con metadato storico immutabile. Una cattura desktop conteneva ancora la dissolvenza della conferma: non promossa come evidenza visiva finale.
- R8-VISIVO: sei browser PASS, acquisizione dopo chiusura completa della conferma, sei immagini sintetiche ispezionate. Tre catture tipologie byte-identiche alle precedenti già ispezionate; profili desktop/tablet/mobile controllati. Le catture mobile possono includere la barra fissa condivisa nella parte superiore: nessun ridisegno di quel componente. Nessun overflow orizzontale nei form verificati.

Unico ambiente: VM `schedine-sviluppo-interno`, PHP 8.3.36 / MariaDB 11.8.9 / PHPUnit 11.5.57, namespace ufficiale senza rete esterna, DB e filesystem effimeri. Build/GEO, identità processi/DB/HTTP e 23 rifiuti di override PASS prima delle migrazioni. SOAP double e client SOAP realmente serializzato in memoria dal WSDL locale: nessun client remoto autenticato. Entrambi i runtime finali rimossi dall’avviatore. Comandi integrali, impronte e limiti in [verifica.json](evidenze/tipologie-appartamento-2026-10-27/verifica.json). Le prove sono sul worktree non committato basato su `4a831f80`; impronte dei sorgenti verificate contro il candidato, senza sommare esecuzioni sovrapposte. Nessun PASS della discovery globale dichiarato.

I controlli coprono: cinque tipologie salvate e recuperate, tre girasoli per Agriturismo, ritorno ad Alberghiera; ID corretto/assente/invalido/zero/zeri iniziali; stesso destinatario fra Test e Send; singolo/famiglia/gruppo a 168 caratteri; risposta positiva non presentata come accettazione, rifiuti/parziali/timeout/malformate, nessun retry cieco; ruoli e due strutture, cifratura/privacy e minimizzazione con riferimento storico conservato. Regressioni Questura standard, ISTAT/Ross1000, Tassa, Schedine e Web Check-in comprese. Non modificati i rispettivi servizi ISTAT/Tassa, generatore Questura, formule fiscali o valori storici.

## Capacità, pubblicazione e ripresa concreta

Lettura finale dopo R8 e pulizia: **VM 2.782.982.144 byte liberi (circa 2,59 GiB)**. Il precedente percorso conservativo C5 qualificato stimava **5.569.875.968 byte (circa 5,19 GiB)** per PREPARE/S0/recuperi/riserve, con 1 GiB VM già compreso. Deficit rispetto a quel percorso: **2.786.893.824 byte**, non ridotto per supposizione. È una stima storica conservativa, non un nuovo PASS di capacità sul candidato. [Protocollo di capacità esistente](QUESTURA-PIANO-CAPIENZA-2026-10-27.md): fermarsi prima di PREPARE/RUN se lo spazio non basta. Nessuna manutenzione, deposito produttivo, backup operativo, S0 o DDL avviati da questa task.

Proposta concreta, **non eseguita e da autorizzare separatamente**: VM 44→48 GiB, preceduta da arresto ordinato, backup integrale cifrato/autenticato su Extra con chiave fisicamente separata, decifratura integrale e verifica degli originali; poi crescita esclusiva del disco/XFS e riavvio con riscontro del quotidiano. La proiezione aggiunge 4 GiB e porta il libero teorico a circa 6,59 GiB, con circa 1,40 GiB oltre la stima C5. Il libero effettivo va rimisurato: nessuna promessa di PASS anticipata. Nessuna cancellazione di backup, recuperi precedenti, cache o lavori estranei proposta come automatismo. Mac/Extra misurati nel consuntivo di capacità; riserve 8/40 GiB da riattestare prima della sequenza. L’ampliamento infrastrutturale non è incluso implicitamente nella correzione applicativa.

Dopo risorse conformi: revisione del candidato e gate Git, nuovo PREPARE e recupero indipendente, nuovo S0 cifrato verificato anche su Extra, C5 autorizzata per le sole due migrazioni elencate sotto, QA online sintetica e pulizia per ID propri, confronto completo dei dati precedenti con esclusione dei soli delta approvati e monitor finale. Nessun checkpoint storico riusato. Questura/ISTAT OFF, nessun Test/Send reale. Nessun merge o deploy dichiarato prima dell’esecuzione effettiva.

Migrazioni previste:

1. `2026_10_27_120000_add_questura_profilo_to_struttura.php`: due colonne nullable su `struttura`, un JSON nullable su `questura_transmissions`; due ALTER, nessuna tabella nuova e nessun backfill.
2. `2026_10_27_120100_complete_tipologie_turistiche.php`: solo aggiunte ai cataloghi. Sulla situazione letta, attesi 1 tipologia, 3 classificazioni e 6 pivot nuovi; nessun record precedente riscritto. Attese 161 migrazioni / 66 tabelle dopo rilascio, contro 159/66 attuali. Le quantità sono da riconfermare al preflight, non un’autorizzazione a forzare lo stato.

Produzione finale riconfermata in sola lettura: **`ac7dd4a9c88a6e979f073a60fe8b715523f9821e`**, Git pulito, 159 migrazioni / 66 tabelle, barriera assente, Questura e ISTAT OFF. Gli 8.700 file originali del checkout operativo sono byte-identici all’inizio. Nessuna sincronizzazione dei nuovi sorgenti su Herd: il lavoro è nel worktree dedicato.

**QA online, backup/recupero di rilascio e monitor non eseguiti per il blocco di capacità; nessuna pubblicazione di questo candidato dichiarata.** Pendenti: revisione indipendente, risorse e C5, conferma della pratica e posizione effettiva di ogni struttura, profilo dell’utenza, appartenenza dell’ID attivo, accettazione reale Questura/Ross1000/StayTour, categorie fiscali non certificate. Raggruppamenti rinviati alla fase successiva.

## Registro Git e consegna

Ramo locale `codex/tipologie-appartamento-20261027`; commit applicativo **`5fcee89fa2d34c7f63d2bcba8ee19012c5d6bfa0`**. Le 21 impronte dei 15 sorgenti applicativi/migrazioni e dei 6 file di test sono identiche ai blob del commit; nessun cambiamento dei sorgenti successivo alla qualifica. Il successivo commit di consuntivo modifica soltanto documentazione. Checkout operativo originale e main preservati.

**Push NON eseguito e PR NON creata.** La revisione automatica ha rifiutato il comando prima dell’avvio: pubblicare i 25 file su GitHub pubblico espone codice e documentazione all’esterno e richiede, secondo il revisore, un consenso specifico dell’utente. Nessun tentativo con altro strumento, mirror o percorso alternativo. La descrizione PR è pronta privatamente; dopo consenso al push si potranno pubblicare il ramo e la PR in bozza, senza implicare merge o deploy. Il blocco Git è distinto da quello di capacità C5. Nessuna revisione indipendente o approvazione del candidato dichiarata.

Esito complessivo **PARZIALE**, rischio di rilascio **MEDIO** per le due migrazioni ancora da applicare sotto C5. Maestro **AGGIORNATO**. Prossimo passo: consenso specifico al push pubblico e decisione sulle risorse, poi preflight aggiornato e prosecuzione del flusso già descritto.

| Voce obbligatoria | Esito |
| --- | --- |
| Codice modificato | SÌ, solo worktree dedicato |
| Test modificati / eseguiti | SÌ / SÌ, qualifica 390 test e 6 browser nei rispettivi runtime |
| Documentazione modificata | SÌ |
| Configurazione operativa modificata | NO |
| Schema/dati modificati | SÌ nei soli database sintetici effimeri; NO negli ambienti operativi |
| Commit | SÌ, locale |
| Push / PR / merge | NO / NO / NO |
| Deploy / trasmissioni reali | NO / NO |


## R10 e ampliamento conclusi

Backup integrale PASS: 12 file, 27.042.126.393 byte cifrati su Extra, autenticati e decifrati integralmente; originali riletti identici, chiave separata sul Mac. VM44→48GiB PASS, XFS45.880.217.600→50.175.184.896 byte, 6.993.809.408 liberi dopo riavvio. DB quotidiano/84 protetti/HEAD945ef553/SELinux identici; solo configurazione disco variata. Nessun backup o originale rimosso. [Ricevuta risorse](evidenze/tipologie-appartamento-2026-10-27/risorse.json).

P2 PR38 riprodotto e corretto nel solo completamento catalogo: CAV creata se assente prima delle associazioni ai soli. Seeder generale invariato e non eseguito. R9 iniziale errore di setup per seeder non incluso; R9B rifiutata dal launcher, nessun bypass. R9C riproduce con fixture il catalogo popolato dopo le migrazioni: 3 test /59 asserzioni, un fallimento atteso con sola Nessuna. R10 finale: **391 test /5.414 asserzioni e sei browser PASS**, sei catture ispezionate; runtime effimero rimosso. Non si sommano prove sovrapposte. Commit applicativo corretto `dc26e25c84761b7ed72cb5472c4b1472a9a44140`, 21 impronte corrispondenti a R10. Vecchio rilievo risolto sulla PR aggiornata; nuova revisione ancora da attestare.

Kit C5 privato da rigenerare sul nuovo candidato. Verifica conservativa del delta al sigillo, prima dell’apertura e dopo la chiusura del monitor runner, riusando il dump già prodotto: sole tre colonne NULL, 1 tipologia/3 classi/6 relazioni e due ledger; nessuna nuova tabella. Prove sintetiche del confronto PASS (quattro alterazioni respinte), hook PASS (delta ammesso, alterazione e S0 mancante respinti senza promozione). Non costituiscono C5 produttivo. Produzione ultima lettura ac7dd4a9/159migrazioni/66tabelle, OFF invariati. PREPARE/S0/RUN/DDL/QA/monitor non eseguiti.


## Riordino JSON — R12 finale

Seconda revisione PR38 sul candidato0b769152: P2 sul confronto del contesto JSON dipendente dall’ordine delle chiavi. R11 riproduce il solo riordino con contenuto identico: 14test/164asserzioni, un fallimento atteso; ID di tipo diverso, chiave assente e chiave aggiunta già respinti. Correttivo minimo nel controller: copie dei due contesti ordinate per chiave, confronto rigoroso invariato. Nessun cambiamento al destinatario, agli snapshot immutabili o ai metodi di trasporto.

**R12: 395 test/5429 asserzioni e sei browser PASS**, sei catture ispezionate, runtime effimero ripulito; 21 impronte conformi al commit applicativo `128e4ef2d38dc0a1bcd5a20f261a908aa7ab9488`. Non sommare con R10. La prova simula il riordino su MariaDB isolata e non attesta una nuova integrazione MySQL operativa.

Deposito/preflight del kit precedente0b769152 PASS (nove sonde, chiavi/infrastruttura preservate), ma nessun PREPARE/S0/RUN/manutenzione/DDL eseguito: kit conservato inattivo. Nuovo kit da rigenerare sul candidato corretto dopo revisione. Backup e crescita48GiB già PASS, nessuna ripetizione implicita; Questura/ISTAT OFF e produzione ac7dd4a9 invariati.


## Stato di pubblicazione dopo revisione R12

Revisione automatica del codice conclusa sul candidato **79b5d7e77a02c2fd98e9ddae24016ad0231d1e5b senza rilievi**, distintamente dalla revisione automatica delle autorizzazioni. Quest’ultima ha respinto, prima dell’esecuzione, il merge PR38 in main (richiede consenso specifico all’integrazione distinto da push/PR) e il PREPARE (richiede consenso specifico al payload produttivo sensibile DB/file/config e alle destinazioni private Mac/VM/Extra). Nessun aggiramento, nessun consenso ulteriore presunto. Richiesta specifica inviata all’utente.

Kit79b5d7e7 depositato e preflight PASS, ma nessun PREPARE/S0/RUN, manutenzione, DDL o fixture online. Produzione riconfermata ac7dd4a9, Git pulito, 159migrazioni/66tabelle, barriera assente e Questura/ISTAT OFF; PR38 aperta e non integrata. Presidio temporaneo Mac proprio chiuso (codice130), VM lasciata attiva come all’inizio. Backup integrale e crescita48GiB restano PASS. La consegna del candidato corretto e qualificato è concreta; rilascio e accettazione online restano BLOCCATI dall’autorizzazione automatica, non da un fallimento di R12.

Il nuovo consenso richiesto comprende merge PR38 e rilascio protetto del candidato con backup cifrati del DB operativo (eventuali dati personali), file/upload persistenti e configurazioni necessarie, inclusi .env/APP_KEY e materiale infrastrutturale del perimetro S0; custodie private Mac e VM e disco Extra, chiave separata nella VM, recupero indipendente. Due sole migrazioni nominate, QA sintetica e monitor; nessuna trasmissione reale e flag OFF invariati. Nessun backup/segreto pubblicato in Git. [Ricevuta dello stop](evidenze/tipologie-appartamento-2026-10-27/stato-pubblicazione.json).

Preservazione finale: tutti gli **8.700 file del checkout originale byte-identici**, nessuna differenza; fonte privata `preservazione-originali-dopo-ripresa.json`. Nessun test Laravel sull’host. Prossimo passo: consenso specifico ai due gate, poi nuovi PREPARE/S0/C5 e QA/monitor sul candidato fissato, senza riusare checkpoint.

| Indicatore corrente | Esito |
|---|---|
| Codice / test / documentazione modificati | SÌ / SÌ / SÌ, nel worktree dedicato |
| Configurazione modificata | SÌ, solo disco VM44→48GiB; nessuna configurazione operativa dell’app |
| Schema/dati produttivi modificati | NO |
| Test eseguiti | SÌ, R12 isolata395/5429+6browser; diagnostici distinti |
| Tenant / sicurezza | Due tenant, ruoli, cifratura, retention e doubles verificati; canali OFF |
| Commit / push / PR | SÌ / SÌ / SÌ |
| Merge / deploy / trasmissioni reali | NO / NO / NO |
| Stato complessivo / rischio residuo | PARZIALE; pubblicazione BLOCCATA per consenso specifico, rischio MEDIO delle due migrazioni ancora da verificare operativamente |


## Pubblicazione e QA concluse, monitor finale in corso

Consenso specifico «Sì, autorizzo entrambi» acquisito per merge PR38 e rilascio con backup produttivi cifrati Mac/VM/Extra, chiave separata VM e recuperi indipendenti. I precedenti rifiuti automatici restano storici, non blocchi attuali. PR38 integrata in main con merge **03e888a3**; produzione **79b5d7e77a02c2fd98e9ddae24016ad0231d1e5b**, applicativo qualificato **128e4ef2**,21impronte identiche alla revisione senza rilievi e alla main integrata. Consuntivo separato soltanto documentale. Nessun nuovo sviluppo o test Laravel sull’host.

PREPARE PASS e recupero indipendente completo; PREPARE/S0 autenticati e decifrati integralmente anche su Extra, chiave separata VM.55campioni di quiete, capacità residuale5,42GB contro3,46GB stimati comprensivi di1GiB riserva, nessun ritiro backup/derivati. Un solo RUN autorizzato: **S0 199,999s/C5 307,912s/528campioni PASS**; budget1800/600/1379/421 e trasferimento120s invariati. Guardiano concluso, disarmato, inattivo e disabilitato.

Due sole migrazioni applicate: ledger159→161,66tabelle immutate. Confronto rigoroso del dump S0 al sigillo prima dell’apertura e nuovamente dopo: tre colonne nullable con tutti i valori precedenti NULL,1tipologia/3classificazioni/6relazioni e2registrazioni nel ledger; dati, schema, indici, ID e timestamp precedenti preservati, nessun seeder/deduplicazione/backfill.331sorgenti/protetti e104persistenti conformi, Git/FPM/nove sonde PASS, credenziali/APP_KEY invariati e canali OFF.

QA online **R5 PASS** nelle tre larghezze1440/768/390: cinque tipologie salvate/rilette, Agriturismo tre girasoli, ritorno a Hotel, Gestione Appartamenti con ID0 da zeri iniziali e ritorno a Standard senza ID; tenant403, reception sola lettura, no-store, zero erroriJS/POSTestranei e nessuna trasmissione. Sei catture ispezionate. R1 errore del solo adattatore fixture su chown residuo: rollback DB/schema verificato integralmente esclusi AUTO_INCREMENT, nessun restore/reset. R2–R4 interrotti nei soli helper browser (conferma assente; tokenCSRF meta/form; reception senza form), diagnostici e runtime conservati/verificati prima del ritiro. Nessuna modifica applicativa per rendere verde la QA e nessuna somma delle ripetizioni.

Fixture e sessioni proprie rimosse: HMAC identici sulle66tabelle,4sessioni residue proprie ritirate, nessun restore/reset; soli AUTO_INCREMENT esclusi.104persistenti identici a S0 nei contenuti e metadati, esclusi soli mtime directory. Runtime finale proprio rimosso dopo12file verificati; clone dipendenze preservato. DB quotidiano/84protetti/HEAD945ef553/SELinux identici anche dopo C5/QA; riserve VM1/Mac8/Extra40GiB rispettate. Monitor server e Mac≥900s avviati **dopo tutte le pulizie**, ancora in corso: nessun PASS finale anticipato. [Pubblicazione](evidenze/tipologie-appartamento-2026-10-27/pubblicazione.json), [QA](evidenze/tipologie-appartamento-2026-10-27/online.json), [monitor](evidenze/tipologie-appartamento-2026-10-27/monitor.json).

Restano distinti: accettazione reale Questura con struttura/utenza/profilo/ID e contenuto concordati, credenziali nel form protetto e autorizzazione specifica prima di Test/Send; accettazione Ross1000 e regole fiscali ulteriori non dichiarate. Agriturismo e altre tipologie non determinano automaticamente profilo Questura, codice Ross1000 o tariffa. File Unico e raggruppamenti esclusi. Il checkout originale Herd resta preservato, non riallineato né migrato implicitamente.
