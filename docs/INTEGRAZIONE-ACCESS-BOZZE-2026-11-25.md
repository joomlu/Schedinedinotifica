# Access → Bozze Schedina — 25 novembre 2026

## Stato e obiettivo

**PARZIALE: integrazione implementata e collaudata nel perimetro indicato; NON ATTIVA ONLINE. BASELINE IN AUDIT.** Creare una bozza dalla conferma del check-in Access, conservando l’indipendenza dei due programmi e la conferma/invio del gestionale Schedina. Richiesta: abilitare Hotel Tanggo Igea Marina e Hotel Tanggo Bellaria, escludere Rimini. «Ceseno» ritirato dal titolare come errore di dettatura.

Rischio ALTO per autenticazione tra servizi, isolamento delle strutture e nuova tabella. Nessun invio a Questura/ISTAT, nessun dato reale trasferito durante il collaudo. Non si modifica il ragionamento di verifica visiva: decisione umana, non confronto biometrico automatico.

## Circuito implementato

1. Nell’invito Access l’operatore sceglie una destinazione configurata oppure Access indipendente. L’identificativo della connessione è riservato alla reception, separato dal nome libero della prenotazione e dai dati modificabili dall’ospite. La selezione compila il nome ufficiale della struttura.
2. Documenti, dati di titolare/componenti, approvazione della reception, selfie, confronto visivo e arrivo mantengono il circuito esistente. Non si trasmettono inviti non confermati.
3. Alla conferma del check-in Access prepara un’istantanea dei dati verificati originali, non dello storico ridotto. La memorizza nella coda SQLite persistente. Nessuna fotografia, selfie, link/token ospite, chat o nota privata sulle eccezioni nel payload.
4. POST JSON `/api/access/v1/checkin`, versione 1, con Bearer distinto per struttura e `X-Access-Connessione`. Solo HTTPS e destinazione di configurazione, senza reindirizzamenti. Credenziali mai nel browser.
5. Il ricevente ricontrolla connessione abilitata, hash del token, utente attivo, appartenenza alla struttura/proprietario e servizio attivo. La destinazione non deriva da ID presenti nel payload. Le connessioni sono disabilitate per impostazione predefinita e installabili soltanto dall’amministrazione.
6. Transazione con blocco della struttura: crea `schedina.circuito=bozza` e i componenti nella stessa struttura. Riusa validazione parziale, costruzione payload, normalizzazione componenti e numerazione esistenti. Nessuna creazione automatica di clienti definitivi o consenso privacy inventato.
7. Il riferimento univoco struttura + UUID check-in e l’impronta del corpo impediscono duplicati. Un retry identico ritorna la stessa schedina anche se nel frattempo confermata. Dati diversi rispondono 409: nessuna sovrascrittura di lavoro dell’operatore. Versione 1 non sincronizza le modifiche successive.
8. Access mostra attesa, bozza ricevuta, revisione o scadenza nel check-in e nel soggiorno. Il pulsante apre la bozza esistente; l’accesso rimane soggetto ai normali permessi/selezione della struttura in Schedina. Errori temporanei ritentati con attesa crescente; 401/403/409/422 richiedono revisione e riprova esplicita.
9. Dopo conferma del ricevente il payload della coda viene cancellato e resta la ricevuta minima. Un payload non consegnato scade entro il termine del documento e comunque entro 30 giorni; non si inviano dati scaduti. All’avvio e nella manutenzione periodica vengono recuperati check-in confermati rimasti senza coda dopo un’interruzione.

## Strutture verificate sul server in sola lettura

| Destinazione | Struttura | Proprietario | Reception dedicata | Esito previsto |
|---|---:|---:|---:|---|
| Hotel Tanggo Igea Marina | 94 | 61 | 156 | Abilitata nel pacchetto riservato, non installato |
| Hotel Tanggo Bellaria | 95 | 61 | 157 | Abilitata nel pacchetto riservato, non installato |
| Hotel Tanggo Rimini | 96 | 61 | 158 | Nessuna connessione, esclusa |

I tre hotel e le reception risultano attivi; servizio fino al 31/12/2027 alla lettura del 25 novembre. La tabella `access_importazioni` non esiste ancora online. Non dedurre un’attivazione da questa ricognizione.

## Tabelle e configurazione

| Programma | Tabella | Scopo |
|---|---|---|
| Access | `schedina_collegamenti` | Destinazione fissa dell’invito e operatore proprietario |
| Access | `schedina_trasmissioni` | Coda, scadenza, ritentativi, ricevuta ed errore sintetico |
| Schedina | `access_importazioni` | Collegamento univoco check-in/struttura/bozza, impronta e avvisi |

I dati gestionali rimangono nelle tabelle esistenti `schedina` e `componenti`; proprietario risolto tramite struttura. Il riferimento CI e quello di prenotazione sono visibili nelle osservazioni; UUID esterno anche in `id_prenotazione_esterna` e registro di importazione.

Access: nuovo parametro `schedina` di `startPilot` e file privato indicato da `TANGGO_SCHEDINA_CONFIG`, con URL e connessioni contenenti id/nome/struttura_id/token. Schedina: `config/access.php`, variabile privata `ACCESS_CONNECTIONS`, con enabled/struttura_id/proprietario_id/user_id/token_hash. Sono predisposti due token indipendenti; i file riservati 0600 sono esclusi dal repository e dagli archivi sorgenti. Per disabilitare una struttura revocare la sua connessione e rimuoverla dalla configurazione Access. Non è stata aggiunta una nuova schermata Superadmin.

Migrazione additiva proposta: `2026_11_25_120000_create_access_importazioni_table.php`. Nessuna colonna di tabelle esistenti modificata; nessun DDL produttivo eseguito. Le due tabelle Access sono create all’avvio soltanto nell’archivio reception del servizio interessato.

## Campi e limiti dichiarati

Nome/cognome/sesso, nascita, cittadinanza, residenza, documento, rilascio/scadenza/autorità del titolare; dati obbligatori dei componenti, ruoli 16–20, arrivo/partenza, quantità persone/camere/letti, contatti del titolare e riferimento prenotazione. Cittadinanza del titolare in `oa_city_nac`, dei componenti in `city_nac`: non confonderle con il luogo di nascita. Tipo alloggiato risolto dal catalogo, non da ID esterni. Tipo documento risolto per descrizione non ambigua; GEO riconciliato per etichette, senza copiare ID fra database. Ulteriori dati del documento senza campo dedicato (codice fiscale, statura, occhi, categorie patente) nelle osservazioni.

Il luogo di nascita del titolare oggi è un testo libero in Access: Stato/regione/provincia richiedono ancora completamento o verifica in Schedina. Riferimenti GEO non riconosciuti e tipo documento assente dal catalogo sono segnalati. Privacy, classificazioni ISTAT e vere esenzioni restano da verificare nel gestionale. Soggiorno in giornata ammesso nella bozza con avviso; la validazione definitiva rimane invariata. Nessuna attestazione di piena conformità normativa o accettazione enti.

Gli inviti precedenti non vengono collegati automaticamente a una struttura sulla base del nome. Nessun trasferimento retroattivo delle tre schede reali; questo rilascio riguarda nuovi inviti esplicitamente collegati. Il campo `customer_id` resta nullo nella bozza: anagrafiche e conferma definitiva seguono i percorsi esistenti.

## Test ed evidenze

- Access Node 24: **83 test positivi**, zero fallimenti, suite `node --test tests/*.test.mjs`. Compreso il circuito completo con login, invito, documento fittizio, approvazione dati, selfie fittizia, confronto umano simulato, arrivo, conferma check-in, coda, ricevuta, riavvii e storico. Trasporto simulato.
- Ricevente Laravel: **61 test e 913 asserzioni positivi** nella sola VM ufficiale Lima `schedine-sviluppo-interno`, PHP 8.3.36/MariaDB 11.8.9, copia privata del candidato e risorse effimere. Selezione `tests/Feature/AccessImportazioneTest.php`, `tests/Feature/WebCheckinLifecycleTest.php`, `tests/Feature/StrutturaAuthorizationTest.php`, con inclusione esplicita dei cinque nuovi sorgenti applicativi/configurazione/migrazione. Build/GEO e 23 rifiuti di override positivi.
- Contratto: payload sintetico realmente prodotto dal circuito Node 24 acquisito e provato nel ricevente Laravel, verificando anche nascita e autorità di rilascio. Non è una consegna HTTPS fra i servizi di produzione.
- Browser Chrome locale: scelta Igea Marina/Bellaria, assenza Rimini, nome struttura canonico, creazione e annullamento invito fittizio, stato della destinazione visibile, nessun overflow a larghezze 1396/768/390. Nuova pagina dopo la correzione: nessun errore JavaScript.
- Negativi: credenziale errata, connessione assente/disabilitata, reception inattiva/altra struttura, proprietario diverso, servizio scaduto, ID/stato/consenso arbitrari, incongruenze ruolo/totale, data impossibile, oggetto al posto del testo, duplicati/modifiche, rollback componente, risposta altra struttura, indisponibilità/retry e scadenza della coda.

Evidenze private: `/private/tmp/access-schedina-20261125`, log Node `/private/tmp/access-schedina-node-finale.log`, log VM `/private/tmp/access-schedina-vm-r6.log`. Anteprime `anteprima-invito.png`, `anteprima-mobile.png`. I servizi/dati effimeri dei collaudi sono stati chiusi; copia di lavoro privata nella VM preservata, nessun progetto quotidiano modificato.

### Problemi trovati e corretti durante le prove

Primo avvio VM fermo prima dei test perché PATH non conteneva npm: usato il runtime Node 20 già autorizzato, senza installazioni. Primo test Access lanciato dalla cartella workspace anziché dal repository, ripetuto nella cartella corretta. Laravel inizialmente scartava campi del documento non elencati individualmente: corretta l’acquisizione dei dati testuali validati e dimostrata la conservazione delle date/autorità. UI iniziale cercava `.fields` dopo la sua rinomina: corretto selettore, pagina ricaricata e nuova scheda senza errori. Una prova aggiunta usava il nome presunto `customers`: corretto il solo test con il modello `Customers` (tabella `clienti`). Fallimenti intermedi conservati; nessun controllo indebolito.

## Rilascio ancora da eseguire

Il Maestro richiede conferma del perimetro, migrazioni esplicitamente approvate e rilascio della stessa revisione locale/GitHub/online. Prima del rilascio: confronto nuovo dello stato servito, commit selettivo su ramo `codex/`, revisione e allineamento autorizzato di main; checkpoint fresco cifrato e recupero verificato, configurazioni riservate dei soli hotel 94/95, migrazione additiva, nuova release Access, cache route/config appropriate e sonde. Nessun invio a enti o contatto a ospiti reali. Collaudo online da eseguire soltanto con nuovi check-in fittizi dei due hotel e rifiuto Rimini, controllando una sola bozza per check-in. Non riutilizzare i token di prova.

Ritiro: disabilitare i collegamenti, fermare il trasporto Access e ripristinare il codice con checkpoint qualificato. Dopo la prima importazione non cancellare la tabella né ripristinare ciecamente l’intero database: conservare ricevute e bozze già create e riconciliare i dati. Chiavi e archivi privati seguono il protocollo esistente.

## Chiusura

STATO PARZIALE; codice/test/documentazione modificati; configurazione applicativa predisposta con default disabilitato e segreti fuori repo; schema/dati modificati soltanto nei test effimeri. Tenant/sicurezza verificati nel perimetro sopra. Git: HEAD Schedina `ff21cd855027d3973c2607f7db687fc5a641c58b`, ramo `main`, nuovo lavoro non registrato, precedenti modifiche documentali conservate; Access base `4de727c` con lavoro preesistente non registrato conservato. Nessun commit/push/deploy o trasmissione esterna. Maestro aggiornato. Prossimo passo: conferma concreta del rilascio e della nuova tabella, poi protocollo di pubblicazione e verifica online.

CODICE MODIFICATO SI · TEST MODIFICATI SI · DOCUMENTAZIONE MODIFICATA SI · CONFIGURAZIONE MODIFICATA SI (solo sorgente/default e predisposizione privata locale) · SCHEMA/DATI PRODUTTIVI MODIFICATI NO · TEST ESEGUITI SI · COMMIT NO · PUSH NO · DEPLOY NO · TRASMISSIONI ESTERNE NO.

## Autorizzazione al rilascio

Il 25 novembre 2026 il titolare ha confermato esplicitamente GitHub/main, pubblicazione online, migrazione e attivazione dei soli hotel Igea Marina/Bellaria. Protocollo di rilascio in esecuzione; non equivalere autorizzazione a esito positivo. Manuale operativo: [Dal check-in alla bozza](MANUALE-ACCESS-SCHEDINA.md).
