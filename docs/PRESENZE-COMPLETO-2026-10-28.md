# Presenze — completamento del 28 ottobre 2026

## Perimetro e fonte

Richiesta esplicita: analisi, correzioni, test isolati, GitHub e pubblicazione delle cinque schede informative. Base applicativa online f403cdb9, base GitHub 56085cfd con sole differenze documentali. Copia separata, ramo codex/presenze-completo-20261028; checkout originario, indice e lavori estranei preservati. Nessuna migrazione, modifica prenotazioni, ospiti, disponibilità o attivazione enti.

Percorso: Statistica → Presenze, GET /statistica/presenze e due GET di stampa. PresenzeController, PresenzeReportService, viste statistica/presenze; modelli Schedina, Componenti, SchedinaCamera, Struttura, GeoNazione. Le cinque schede condividono lo scope effettive(): is_arrive falso e circuito schedina oppure legacy NULL. Arrivi e bozze non sono soggiorni effettivi. I componenti e le camere vengono caricati con vincolo struttura esplicito, senza join moltiplicativi. StrutturaAccess verifica ruolo, appartenenza, query e selezione originale di sessione anche nelle stampe. Il modulo ha soli ingressi GET e non salva dati operativi.

## Correzioni e regole

- Arrivi e partenze sono persone con la rispettiva data inclusa nel filtro; nel dettaglio non si contano più come arrivi tutti i soggiorni sovrapposti.
- Presenze: persone per notti dell’intersezione [arrivo, partenza) con [dal, al+1), giorni civili Europe/Rome. Ultimo giorno del mese normalizzato a mezzanotte prima dell’intersezione.
- Saldo iniziale: arrivo precedente al primo giorno e partenza uguale o successiva. Include quindi chi parte il primo giorno, con zero pernottamenti quel giorno. Saldo finale = iniziale + arrivi − partenze. Il totale periodo prende soltanto il primo saldo iniziale, non somma i saldi mensili.
- Residenza individuale: or_country del titolare e country dei componenti, risolti nel catalogo GEO; mai nazionalità o paese di nascita come ripiego. Italia, estero e non disponibile separati. Il filtro dettaglio conta le persone selezionate anche nei gruppi misti; nominativo e residenza mostrati identificano il titolare.
- Conteggio uniforme: maggiore fra persone dichiarate e nominativi collegati; eventuali persone senza anagrafica restano sconosciute, con avviso. Età all’arrivo con EtaOperativa, senza trasformare dati mancanti in adulti; situazione giornaliera segnala età e residenza sconosciute.
- Situazione giornaliera: pernottanti, movimenti e elenco dei soggiorni presenti distinti. Il dataset presenti_rows prima scollegato è ora visibile.
- Camere reali: numero camera come identificativo, distinto fra le assegnazioni attive; in assenza di assegnazioni si usa la quantità legacy room. Persone pernottanti come occupazione dei posti letto, distinta dalle camere. Capacità giornaliera zero fuori apertura stagionale; data di chiusura inclusa, coerentemente con SchedinaSoggiornoValidation.
- Percentuale periodo: somma notti occupate / somma notti disponibili, non media semplice dei rapporti giornalieri. Numeratori e denominatori visibili, capacità zero N/D, valori oltre100% non limitati e accompagnati da avviso.
- Filtri validati frontend/backend: date reali ordinate, mesi ordinati, categorie e schede in allowlist; intervallo personalizzato massimo1095giorni. Filtri conservati nei form GET, periodo mensile trasferito a dettaglio e occupazione, giorno convertito al primo giorno del periodo; tab conservata nell’URL. Stampe condividono servizio e riepilogo. Nessun CSV del modulo risulta implementato: non aggiunto.
- Riepilogo vuoto: un solo messaggio, senza dodici tabelle di zeri; eliminate le due colonne duplicate delle presenze per nazionalità.
- Prestazioni: una sola lettura annuale con relazioni eager e catalogo GEO in memoria; sottointervalli riutilizzati nella stessa istanza richiesta. Prova su25soggiorni annuali con massimo4query, non benchmark di carico produttivo.

## Limiti del modello

Non esistono stati autonomi cancellato/no-show sulle schedine effettive, né inventario di unità fuori servizio, storico capacità o cambi camera datati. Cancellazioni e no-show devono restare fuori dal circuito effettivo; un record erroneamente inserito come soggiorno effettivo non è distinguibile a posteriori dal report. Non introdotti stati o prenotazioni fittizi per simulare funzioni assenti. La disponibilità storica e le assegnazioni sono ricostruite solo dai dati correnti; limite dichiarato nell’interfaccia, senza certificare capacità storiche inesistenti. Non è un report fiscale o un invio ISTAT. La coerenza interna non certifica l’esattezza delle anagrafiche reali.

## Prove e stato

Runtime esclusivo ufficiale Lima schedine-sviluppo-interno, namespace rete/PID/mount, SELinux Enforcing, PHP8.3.36, MariaDB11.8.9, DB e filesystem effimeri, fixture sintetiche. Nessun test Laravel su Herd o DB reale.

R1:5test/52asserzioni,1fallimento sulla capacità stagionale; corretto il supporto DateTimeInterface già convertito dal modello. R2:5/56PASS,3browserNONPASS perché il test non completava le conferme reali del login; correzione soltanto dell’adattatore browser. R3:42/194,1NONPASS per aspettativa account inattivo403 anziché revoca sessione e redirect302 al login; protezione invariata, aspettativa corretta senza indebolire tenancy. R4:42test/195asserzioni e3browserPASS, immagini in dissolvenza non accettate come prova visiva. R5:8/71 e3browserPASS con attesa delle transizioni e immagini stabili. R6:9/75 e3browserPASS dopo il raccordo del periodo al primo accesso. R7:43test/199asserzioni e3browserPASS,15viste fotografate, inclusa percentuale dei posti letto e controlli di periodo personalizzato. Tutti i23rifiuti di override e build/GEO PASS. Selezioni sovrapposte non sommate.

Selezione R8: PresenzeCompleteTest, StrutturaAuthorizationTest, StrutturaSelezioneTest, SchedinaSoggiornoTest; fixture presenze-fixtures.php; browser presenze-complete.playwright.spec.js a1440/768/390. Risultati sovrapposti non sommati. Formula attesa indipendente per gennaio2026: saldo iniziale3, arrivi3, partenze5, finale1, presenze7; confronto fra mesi, dettaglio, somma giornaliera e movimenti. Inclusi primo del mese, dicembre/gennaio, febbraio2024bisestile, gruppi e paesi misti/sconosciuti, esclusione circuiti non effettivi e altro tenant, date rettificate, camere ripetute, periodo vuoto, capacità variabile/zero/150%, ruoli e sessioni contraffatte, stampe e filtri errati; snapshot soggiorni prima/dopo GET identico.

Qualifica isolata VERIFICATA nello scope indicato; pubblicazione ancora PENDENTE C5. [Ricevuta](evidenze/presenze-2026-10-28/verifica.json). BASELINE IN AUDIT generale invariata.

Revisione finale: mantenuto il collegamento preesistente Stampa dettaglio mese e rimossa la colonna duplicata Persone dall’occupazione. R8 sul candidato finale:43test/199asserzioni e3browserPASS,23override/buildGEO conformi; stampa mensile controllata anche tramite destinazione del collegamento. Nessuna modifica delle formule dopo R7.
