# Diagnosi 403 proprietario — 18 novembre 2026

**CORREZIONE COLLAUDATA NELLA VM UFFICIALE; RILASCIO ONLINE IN PREPARAZIONE. BASELINE IN AUDIT.**

Segnalato 403 su `/struttura` dopo l’accesso diretto alla nuova area globale di condivisione. Lettura dei registri del solo vhost, con query/IP/ID omessi: area proprietario 200, Dati struttura e Nuovo cliente 403 senza selezione; Nuova schedina rinvia già al selettore; dopo la selezione Dati struttura risponde 200. Servizio FPM attivo, barriera aperta e codice online c1816e7. Nessun blocco generale dimostrato dalle richieste osservate.

Nel codice `StrutturaAccess::resolve()` l’assenza di un contesto viene gestita con `StrutturaNonSelezionata` per admin e super_admin; il proprietario senza selezione arriva invece al 403. Rimedio immediato comunicato: scegliere l’hotel su `/strutture/seleziona`, poi aprire Dati struttura. Non sono stati cambiati account, autorizzazioni, clienti o soggiorni.

Preparata una correzione minima del solo caso proprietario attivo con strutture autorizzate: invito a selezionare, senza assegnare automaticamente il primo hotel e senza ampliare i permessi. Proprietario senza strutture, ID espliciti estranei/malformati e sessioni non autorizzate restano vietati. Estesi i test esistenti per pagine HTML, JSON e scritture prive di contesto, oltre ai rifiuti di riferimenti estranei. L’area globale di condivisione deve restare accessibile senza scelta di un singolo hotel.

La sincronizzazione verso la copia privata della VM non ha completato la connessione SSH: nessuna prova applicativa è stata avviata. La VM è indicata Running ma non è raggiungibile; il volume SchedineSviluppo non è montato. Montaggio con utility documentata e montaggio normale del volume già identificato falliti. Rilevati due collegamenti preesistenti della stessa immagine esterna. Nessuna riparazione, distacco o spegnimento forzato eseguiti in questa diagnosi. Nessun ripiego su Herd o produzione per eseguire test.

Correzione conservata nel lavoro locale, non committata e non distribuita. Nessun rollback della produzione: vanno preservate anche le eventuali scritture e configurazioni effettuate dagli utenti dopo il rilascio. [Ricevuta sanitizzata](evidenze/condivisione-clienti-2026-11-18/diagnosi-403.json).

Ulteriore riscontro: dopo il passaggio nel selettore, `/struttura` risponde 200 anche alle 14:06:04 e 14:06:06 UTC. La conferma diretta del titolare è stata richiesta, senza cambiare dati o permessi.

## Normalizzazione richiesta successivamente dal titolare

Il titolare segnala che il percorso resta poco chiaro e chiede scelta obbligatoria della struttura quando necessaria, con verifica delle altre pagine. Il candidato ora comprende anche il ritorno alla pagina GET originaria dopo la selezione, senza ripetere POST/PUT o conservare i dati inviati. La destinazione è una sola path interna validata, viene consumata una volta e non prevale sulla scelta esplicita della topbar. URL esterni, protocol-relative, backslash e controlli non sono accettati. Nessun hotel assegnato automaticamente.

| Pagine | Percorso candidato |
| --- | --- |
| Dati struttura, clienti/nuovo/import/export, utenti della struttura | Gestore comune: proprietario senza selezione ma con strutture autorizzate → selettore → pagina richiesta |
| Questura, ISTAT, presenze e stampe, gruppi/cataloghi protetti | Stesso gestore comune; regole di autorizzazione e invii invariati |
| Nuova/modifica schedina, configurazione Tassa, elenco/nuovo Web Check-in | Eccezione comune sul solo ingresso senza contesto; stesso ritorno alla pagina richiesta |
| Dashboard, calendario generale, strutture del proprietario e condivisione clienti | Pagine generali: non viene imposta una struttura singola |

Il selettore conserva grafica e pulsante esistenti: aggiunto soltanto il campo nascosto del contesto. Preparata prova browser su cinque ingressi operativi e test per rientro, autorizzazioni, JSON e assenza di ripetizioni delle scritture. Sintassi di 7 PHP e dello spec positiva; esecuzione dinamica ancora pendente a questo checkpoint.

Il titolare ha autorizzato espressamente il recupero della sola VM di collaudo: arresto anche forzato se l’ordinato fallisce, sgancio ordinato dei collegamenti duplicati e rimontaggio unico. Riparazioni filesystem e produzione escluse. Tentativo di arresto ordinato avviato; seguito da registrare.

## Collaudo completato dopo il recupero autorizzato

VM recuperata: arresto ordinato non completato, spegnimento forzato autorizzato, sgancio ordinario dei due collegamenti duplicati, rimontaggio unico dell’immagine originale e riavvio READY/SSH. Un solo collegamento confermato, 43 GiB liberi e zero servizi falliti. Nessuna riparazione manuale o modifica alla produzione per recuperare il collaudo.

**157 test PHP e 2.417 asserzioni positivi in due sessioni**: 109/1.207 per accessi/autorizzazioni/condivisione e 48/1.210 per navigazione schedine, picker clienti, gestione clienti, Web Check-in e Tassa. Matrice 17 ingressi per tre ruoli: scelta richiesta, ritorno alla pagina richiesta e strutture estranee vietate. Verificati anche URL esterni/alterati, proprietario senza strutture e mancata ripetizione delle scritture.

**Browser positivo**, un percorso composto da cinque ingressi: Dati struttura, Nuovo cliente, Nuova schedina, Web Check-in e Tassa. Per ogni ingresso: proprietario senza selezione → pagina di selezione → Hotel prova B → pagina richiesta. Prima prova fermata dal dialogo informativo davanti al pulsante; corretto il solo spec per premere OK, poi riprova positiva in 53,3 secondi. Nessun cambio applicativo necessario per quell’errore del test. Guardie DB/processi/HTTP e 23 rifiuti di override, build/GEO positivi; nove impronte sorgenti confrontate fra Mac e VM.

[Esito e impronte](evidenze/normalizzazione-accesso-2026-11-18/esito.json). Nessuna migrazione, modifica di autorizzazioni o di dati prevista dal rilascio: solo codice, campo nascosto del selettore e invalidazione viste/opcode. La pubblicazione non è ancora attestata a questo checkpoint; i passaggi precedenti rimangono come cronologia.
