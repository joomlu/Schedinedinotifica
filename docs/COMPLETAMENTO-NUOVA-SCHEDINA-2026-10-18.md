# Completamento Nuova schedina — 18 ottobre 2026

## Perimetro e stato

Intervento autorizzato sul flusso completo e sulla selezione di clienti esistenti; nessuna trasmissione reale agli enti, nessuna fixture personale, nessuna scrittura di prova online. In seguito alla risposta esplicita dell’utente **si conserva il solo intervallo attuale**: nessuna migrazione e nessun archivio inventato dei periodi multipli.

Stato iniziale riletto: locale/GitHub main `9eb8396306b1ed3a91aa25e565dfabe77be23587`; VM e produzione `dbee3028494eca70b99c5b77f06d07c95688689b`. Produzione pulita letta come proprietario; FPM e servizio interno attivi. Modifiche documentali e untracked preesistenti preservati. Le prove nuove, i quattro SHA conclusivi e il rilascio richiedono evidenze proprie; questo documento non li anticipa.

## Discrepanze e interventi

- Lo script di ricerca cercava controlli `search`/`results` assenti dal form; il vecchio collegamento dall’elenco esisteva ma era soltanto un’icona «Apri schedina». Aggiunto il selettore effettivo, ricerca per nome/cognome con attesa 250 ms e massimo dieci risultati, riconoscimento degli omonimi con nascita/località, selezione esplicita con conferma, tastiera, cambio/rimozione e stato senza risultati. Nuovo endpoint dedicato: nella ricerca restituisce i soli dati identificativi minimi; dettaglio personale solo alla selezione autorizzata, nessun dato di soggiorno. Risposte obsolete invalidate e richieste annullate. Azione «Crea schedina» ora leggibile nell’elenco.
- Il riferimento GET cliente non usava la medesima selezione autorizzata del salvataggio; la ricerca della catena poteva includere strutture non assegnate all’operatore. Il nuovo ingresso GET, la ricerca e la selezione sono limitati alla struttura attiva: riutilizzano il cliente per ID e non attivano la localizzazione legacy della catena. Il POST legacy mantiene il percorso autorizzato della catena, ora ristretto alle strutture assegnate. La sua deduplicazione storica non viene dichiarata una nuova fusione automatica sicura. Nessun ampliamento dei ruoli.
- La partenza definitiva accettava lo stesso giorno dell’arrivo, senza confronto con l’apertura. Introdotto confronto server e indicazione live dell’intervallo: apertura/chiusura sono giorni inclusivi, coerentemente con il servizio Tavola A; le notti sono `[arrivo, partenza)`, quindi il giorno del check-out non aggiunge una notte. Date complete senza ricorrenze arbitrarie, anche fra anni diversi. Configurazione stagionale mancante/errata produce un errore utile e collegamento alle impostazioni; bozza conservata.
- Regione e provincia erano obbligatorie anche per l’estero. Ora sono condizionali all’Italia; si riutilizza CustomerWizardValidation mediante un adattatore dei nomi legacy per cataloghi/GEO, gerarchie, CAP, cittadinanza, documento e rilascio. Nascita non futura e non successiva all’arrivo. Contatti restano facoltativi.
- Tipo alloggiato principale e relazione dei componenti verificati sul catalogo ufficiale; singolo, famiglia e gruppo hanno conteggi distinti. Controllo della persona ripetuta e nascita del componente rispetto al soggiorno. Persone e letti restano quantità distinte.
- Gruppi I/II/III verificati con il servizio già esistente, titoli attivi e valori statistici confrontati con i cataloghi della vista. Valori storici invariati in modifica conservati dove previsto; cataloghi globali non modificati.
- Errori server associati ai campi con messaggio, aria-invalid/aria-describedby e apertura della prima scheda errata; input precedente conservato dal percorso Laravel esistente.

## Inventario dei campi e contratti

Le sei schede condividono lo stesso modulo e il medesimo payload di creazione/modifica. Il valore iniziale segue old input, scheda persistita, anagrafica cliente, default esplicito della vista. L’adattatore non aggiorna il master cliente. Data e consenso esistenti vengono copiati senza rinnovare la data; la rimozione del collegamento mantiene i dati inseriti e lo dichiara.

| Scheda / campi | Origine e formato | Verifica / persistenza |
|---|---|---|
| Schedina: arrive/departure | Calendario, ISO Y-m-d, inizialmente vuoti | Obbligatori definitivo, partenza successiva, notti nell’apertura; colonne schedina |
| relationship | TipoAlloggiato ufficiale, codice/descrizione compatibili | Capo 16/17/18, componenti 19/20 coerenti; nessun codice inventato |
| exent | Catalogo tassa configurato; default NO | Codice 777 non selezionabile; motivazione e calcolo nel servizio fiscale, non nel tipo ISTAT |
| cant_people / room / beds | Interi; persone default1, camere/letti da inserire | 1–999; persone = titolare + componenti, nessuna uguaglianza imposta con letti |
| customer_group/subgroup/subgroup1 | Catalogo privato struttura, tre livelli | Dipendenze padre/figlio e struttura, servizio gruppi condiviso |
| type / name / surname / sex | Titoli privati attivi; testi; M/F | Nome/cognome obbligatori max100, titolo opzionale max50, sesso obbligatorio |
| observation | Testo libero soggiorno | Separato dalle note anagrafiche e cliente; nessuna importazione dal soggiorno precedente |
| istat_tipo_turismo / mezzo_trasporto / canale_prenotazione / titolo_studio | Costanti del servizio Tavola A riutilizzate dalla vista | Facoltativi al salvataggio, valori ammessi; completezza distinta nella preparazione XML |
| istat_professione | Testo/categorie suggerite | Opzionale max120, nessun nuovo catalogo obbligatorio |
| id_prenotazione_esterna / fonte_prenotazione | Codice e origine del soggiorno | Opzionali, max100/max30, non copiati dal cliente |
| istat_non_turista | Casella booleana | Esclusione ISTAT già nel servizio; non esenzione fiscale né esonero Questura |
| camere[].numero_camera/posti_letto/note/fonte_camera/camera_esterna_id | Righe camere reali facoltative | Numero max30, posti0–20, note255, origine30, ID100; sincronizzazione esistente transazionale |
| Anagrafica: oa_country/region/prov/city/cap | GEO integrato, Italia gerarchica / estero libero | Nazione e località, regione/provincia condizionali Italia; CAP italiano cinque cifre e appartenenza se catalogato |
| oa_city_nac / oa_date_nac | Cittadinanza catalogo e nascita calendario | Cittadinanza valida; nascita non futura e non successiva all’arrivo |
| Residenza: or_country/region/prov/city/cap | GEO integrato | Gerarchie e CAP come sopra; estero senza provincie italiane obbligatorie |
| or_typeaway/address/num | Tipo via privato attivo e indirizzo/civico | Catalogo e limiti esistenti, persistenza nelle colonne della schedina |
| or_doctype/doc | Tipo documento ufficiale e numero | Validazione catalogo e limiti esistenti; requisito capo distinto dai componenti |
| or_published_date/expire/published/country/city | Date, autorità privata attiva, GEO | Cataloghi e condizione città italiana; nessun nuovo obbligo sui componenti |
| Contatti: customer_phone/cellphone/email/fax | Cliente o inserimento esplicito | Facoltativi, email valida, limiti esistenti; non alterano il master |
| customer_observation/anag_observation | Note cliente/anagrafica | Separate dalla nota soggiorno |
| privacy/marketing/communication_consent e relative date | Consensi indipendenti | La selezione non rinnova le date; copia sulla schedina, nessun aggiornamento silenzioso cliente |
| Componenti: id/name/surname/sex/relationship/exent | Righe normalizzate nel componente integrato | Identificatori limitati al parent, conteggio e relazioni, piano di sincronizzazione transazionale esistente |
| Componenti: country_nac/province_nac/comune_nac/city_nac/date_nac/regione_nac/cap_nac | GEO e nascita/cittadinanza | Normalizzatore condiviso, revisione bloccante conservata; controllo nascita e duplicazione |
| Componenti: country/regione/province/city/typeaway/address/number/cap | Residenza individuale | Valori separati dal titolare; persistenza esistente e ricalcolo della tassa dal dato corrente |
| Tassa: righe e totale | Servizio fiscale e profilo comunale/anno applicabile | Output derivato, nessun importo imposto dal browser; ricalcolo su date/persone correnti, errore visibile se profilo mancante |
| Campi tecnici: customer_id/import_row_id/save_mode/intent/active_tab/component_id/index/intenzione | Contesto e azione esplicita | Autorizzazione server e percorsi bozza/componenti/definitivo/arrivi distinti; non sono nuove regole fiscali |

L’inventario testuale delle occorrenze originali è in `output/flusso-nuova-schedina-2026-10-18/campi-form.txt`. Questa tabella registra i contratti letti/corretti, non certifica automaticamente ogni combinazione end-to-end.

## Fonti ufficiali consultate il 18 ottobre

- [Manuale Alloggiati Web](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/Manuali/MANUALEALBERGHI.pdf) e [FAQ ufficiali Questure](https://questure.poliziadistato.it/statics/45/faq-alloggiati-web-aggiornate-al-16.02.2023.pdf): distinzione capo/componenti e documenti. Preparazione TXT, validazione e invio restano passaggi distinti; nessun invio reale eseguito.
- [FAQ Ross1000 Emilia-Romagna](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/faq-rilevazioni-turismo): «Ospite non turista» esclude le presenze ISTAT, conservando l’obbligo Questura. Il codice corrente filtra soltanto la raccolta ISTAT; nessuna esenzione tassa introdotta. La completezza statistica è verificata dal preparatore XML, non dedotta dalla sola etichetta.
- [Pagina comunale Bellaria](https://www.comune.bellaria-igea-marina.rn.it/it/documenti_pubblici/regolamento-imposta-di-soggiorno-ids) e [regolamento collegato](https://municipium-images-production.s3-eu-west-1.amazonaws.com/s3/602/allegati/amministrazione/documenti-e-dati/atto-normativo/regolamento_ids_2025.pdf): il collegamento corrente rimanda alla revisione2025; non prova da solo l’intero profilo2026. Requisiti residenti, notti, età ed esenzioni confrontati con il servizio; tariffe/versioni2026 non modificate né dichiarate riconfermate da questa sola pagina. La verifica di vigenza resta nel repository dei profili.

## Esecuzioni ed evidenze parziali

Runtime ufficiale VM, namespace senza rete esterna, nuova istanza MariaDB e fixture sintetiche. Baseline:39 test/206 asserzioni e tre browser PASS. Incremento R1: confronto fixture senza fresh errato, corretto nel solo test; R2:49 test/231 asserzioni PASS ma tre browser FAIL per selettore esatto del pulsante che include un’icona; nessun timeout aumentato. R3:49 test/231 asserzioni e tre browser PASS,23rifiuti override/build/GEO PASS. R3 precede gli ultimi controlli anagrafici e componenti: non è attestazione del candidato finale.

## Esito conclusivo del collaudo

R15, candidato applicativo stabile: **190 test / 2.525 asserzioni PASS**, inclusi Web Check-in/Arrivi, ISTAT, Questura, tassa, quattro ruoli, dinieghi, rollback e dimensioni dei campi. Una deprecazione PHPUnit già presente nella suite ISTAT, conservata senza interventi estranei. **Sei browser R15 PASS** a1440/768/390: ricerca dal Nome e dal Cognome, omonimi, tastiera, risposta vecchia trattenuta e rilasciata dopo la nuova, selezione/cambio/rimozione, bozza/ripresa, ingresso dall’elenco, salvataggio definitivo, doppio clic, errore server sulla scheda corretta con input preservato, modifica e rilettura. Zero errori JavaScript rilevati in tali percorsi. **17 guardie**,23 rifiuti di override, build e GEO immutabile PASS.

R10 aveva un errore nella variabile di conteggio del nuovo test browser (posts fuori scope): corretto soltanto il test; R11 positivo43test/692asserzioni e6browser, seguito da R12 unificato, precedente al controllo del cambio struttura. R7 aveva anticipato l’inizializzazione del calendario nel test: attesa della condizione effettiva aggiunta, senza aumentare timeout/retry. Diagnostici e precedenti fallimenti conservati in output, mai conteggiati come PASS. I risultati delle esecuzioni sovrapposte non vengono sommati.

La revisione ha riprodotto in R14 un difetto ulteriore: una bozza di un modulo aperto nella strutturaA veniva creata nella strutturaB dopo il cambio di sessione in un’altra scheda (una riga invece di zero). Corretto con SchedinaFormContext, condiviso tra salvataggio, ricerca e preparazione import: campo del modulo vincolato alla struttura iniziale; HTTP422 prima di ogni scrittura o enumerazione quando il contesto cambia. Chiamanti legacy senza campo conservati. **R17:89test/353asserzioni PASS** sul servizio/contratto import e sui contesti corretti/legacy. R16 aveva esposto l’interferenza del resolver Eloquent rimasto da un contenitore Feature già chiuso nel test Unit: ripetute tutte le suite pure prima delle Feature, senza cambi applicativi, salti o guardie allentate. Suite sovrapposte non sommate. Controller import modificato esclusivamente nella preparazione della nuova schedina, non nella logica d’importazione.

Ulteriori correzioni verificate: conservazione del consenso storico senza data, senza inventare un rinnovo; conservazione delle date consenso durante una modifica; campo esplicitamente svuotato non ripopolato dal master cliente; bozza incompleta ammessa con gli stessi limiti fisici dei campi, compresa capacità dei testi in byte, prima della scrittura SQL. Master cliente non modificato. Nessuna modifica a calcoli fiscali, trasporti, credenziali o provider. Nessuna migrazione.

## Capienza e pubblicazione: PARZIALE

VM32GiB, filesystem32995315712byte. Liberi iniziali1599774720byte. Rimossi12064soli derivati vendor/build con custodieSHA/HMAC e assenza di processi/mount; incremento netto puntuale258883584byte.79packGit identici conservatiCoW con contenuti, proprietà, permessi, date e attributi verificati: incremento netto puntuale259686400byte. Tali incrementi non equivalgono al saldo iniziale/finale durante i nuovi collaudi e le copie di lavoro. Nessun SQL/database/env/chiave/cifrato/upload/logo/diagnostico eliminato, nessuna crescita VM. Ulteriore inventario: cache VM complessive circa91MB, senza un recupero sufficiente dimostrato; gli altri archivi decodificati contengono SQL, inventari e copie di recupero da preservare. Nessuna rimozione aggiuntiva incerta.

**Preflight finale a laboratorio fermo:1663713280byte liberi contro3709771776richiesti; mancano2046058496byte.** Riserva1GiB e stima dal recupero storico completo invariati; nessuna riduzione per far passare il controllo. Mac1142054912byte disponibili nella stessa rilettura. **STOP primaPREPARE**: nessun nuovo S0, manutenzione, RUN, DDL, deploy, acceptance online del candidato o monitor post-rilascio. Backup esistenti preservati; la verifica delle custodie per la pulizia non viene presentata come nuovo checkpoint produttivo o restore del candidato.

Produzione riletta ancora **dbee3028494eca70b99c5b77f06d07c95688689b**, Git pulito, FPMattivo, loginHTTPS200/TLS0. Configurazione FPM SHA2004732c799122f7a1c5d2e1a32fdabab89963007a953c19a3cf2d32903255a0, delta upload precedente conservato. Questi controlli provano la baseline rimasta attiva, non il candidato nuovo online. Nessuna prova con scritture reali o trasmissione enti.

[Prova sintetica versionata e hash](evidenze/completamento-nuova-schedina-2026-10-18/verifica.json); comandi esatti e log in output/flusso-nuova-schedina-2026-10-18/verifica-clienti.py, verifica-browser.py, flusso-r15.log e import-r17.log. SHA del commit/push nella ricevuta esterna output/flusso-nuova-schedina-2026-10-18/ricevuta-git.json, senza autoreferenza nel commit stesso.

**Punto di ripresa:** candidato verificato, SHA e stato commit/push nella ricevuta esterna citata; il rilascio resta bloccato dalla capienza. Recuperare in modo verificabile lo spazio mancante o concordare un intervento infrastrutturale distinto; poi rileggere Git/produzione, ripetere preflight completo, preparare nuovo kit C5 con PREPARE/S0/recupero, pubblicare e completare verifica online e monitor. Non riutilizzare vecchi T0/checkpoint. Processo caffeinate dedicato da arrestare alla chiusura, ricevuta nel suo file output. Lavoro estraneo preservato. BASELINE IN AUDIT invariata.

## Ripresa della chiusura — 18 ottobre 2026, ore 08:31 italiane

**PARZIALE — STOP capienza prima di PREPARE.** Candidato applicativo `945ef553aab29ac437ff194f7cfad081fb7ab7c7` confermato identico su main locale e GitHub. Produzione riletta `dbee3028494eca70b99c5b77f06d07c95688689b`, albero pulito; FPM attivo, login HTTPS200/TLS0, configurazione dedicata SHA `2004732c799122f7a1c5d2e1a32fdabab89963007a953c19a3cf2d32903255a0` invariata. Questi sono controlli della baseline, non del candidato online.

Spazio iniziale: Mac1.215.889.408 byte disponibili secondo df; VM1.663.598.592 byte. APFS fisico interno disk0s2, contenitore245.107.195.904 byte, FileVault attivo; diskutil conferma circa1,21GB liberi. La VM risulta Running nella lettura con i permessi necessari: il precedente avviso Broken proveniva dalla lettura sandbox priva di accesso al boot session ID, non da un guasto dimostrato della VM.

Inventario in sola lettura di /tmp, home e cache: le dimensioni du delle copie CoW non rappresentano recupero fisico garantito. Nessuna rimozione di copie Git, collaudi, SQL, database, backup, cifrati, evidenze, upload o loghi. Cache npm condivise sul Mac lasciate intatte; non è stato provato che siano inutilizzate dai lavori altrui. Unica nuova pulizia: cache DNF della VM, dopo verifica di assenza di gestori pacchetti e descrittori aperti sotto /var/cache/dnf. `dnf clean all` ha rimosso18 file ricostruibili. Saldo VM fra le misure iniziale e finale +72.368.128 byte; variazioni concorrenti del Mac non attribuite alla pulizia.

Nuovo calcolo preliminare C5:1.735.938.048 byte liberi contro3.709.771.776 richiesti, **deficit1.973.833.728 byte**. Riserva1GiB, consumo storico completo939.229.184 e due recuperi nuovi invariati: nessuna soglia ridotta e nessun checkpoint precedente riutilizzato. Non è stato eseguito il preflight integrale di rilascio, poiché il controllo preliminare di capienza lo impedisce. Nessun PREPARE, nuovo S0, verifica di recupero, manutenzione, RUN, deploy o monitor900s del candidato in questa ripresa; nessun test Laravel eseguito. Rimangono valide come evidenze precedenti, non ripetute qui, le prove isolate R15/R17.

Ultima misura ore08:31:29: VM1.735.966.720 byte liberi, root XFS /dev/vda3 da32.995.315.712 byte, servizio interno attivo e SELinux Enforcing; Mac1.409.339.392 byte liberi. Disco VM nominale32GiB, file disk allocato32.517.689.344 byte secondo du. Nessun nuovo backup o ampliamento eseguito. Nessun volume esterno di backup montato osservato sotto /Volumes.

### Proposta concreta, non eseguita

Portare la VM interna **32→36GiB** aggiungerebbe4.294.967.296 byte nominali: il margine lordo sul deficit C5 sarebbe circa2,32GB, da confermare dopo crescita e nuova misura del filesystem. Prima dell'intervento predisporre almeno **8GiB realmente liberi sul Mac** (attualmente mancano7.180.595.200 byte a questo obiettivo prudenziale, che non sostituisce né riduce le soglie C5) e una destinazione di backup cifrata con almeno **40GiB liberi**, scrivibile e verificata. Non basarsi su spazio purgeable o sull'ipotesi di backup CoW gratuito. Nessuna cancellazione automatica proposta di dati protetti o lavoro estraneo.

Sequenza proposta: rilettura configurazione e percorso disco della sola VM interna; arresto ordinato e backup recuperabile integrale di disco/configurazione/identità/EFI, hash sorgente-destinazione e verifica di recuperabilità secondo protocollo vigente; crescita non distruttiva a36GiB con strumenti Lima previsti, senza ricreazione/formattazione; estensione partizione/XFS solo se necessaria e verificata nella configurazione reale, mantenendo UUID e configurazione; avvio e controlli DB/persistenti/env/chiave/upload/loghi, SELinux, servizi e login. Ricalcolare poi capienza Mac/VM e procedere con nuovo preflight integrale, PREPARE, S0 e recupero verificato, C5, verifica online senza scritture e monitor minimo900s. Autorizzazione alla pulizia/pubblicazione conservata; questa proposta non è un ampliamento già eseguito.
