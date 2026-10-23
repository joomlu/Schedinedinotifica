# Anagrafiche unificate — verifica del 23 ottobre 2026

Stato: BLOCCATO ALLA PUBBLICAZIONE DAL GATE C5; implementazione verificata in ambiente isolato e integrata su GitHub. Verifica online del candidato non eseguita. Stato generale del progetto: BASELINE IN AUDIT.

## Causa e correzione

Le stesse righe erano aggiornate con regole differenti nei pannelli: valori omessi potevano diventare default, testi legacy e FK divergevano, l'account poteva essere ricercato per nome/email e il trasferimento lasciava utenti precedenti collegati. I servizi AnagraficaStruttura e AnagraficaProprietario condividono validazione e payload parziali. Il resolver StrutturaAccess controlla relazione e contesto esplicito. Non vengono create copie per ruolo.

Il titolare registra una struttura sul proprio proprietario, inizialmente inattiva; dopo l'alta consulta i maestri. Admin assegnato e Superadmin modificano la stessa riga. Titolare/gestore conservano camere reali e configurazione Questura; reception conserva le proprie funzioni operative. Account, licenze e fatturazione rimangono separati. Credenziali vuote non cancellano i segreti salvati. Una discordanza legacy richiede conferma esplicita; le corrispondenze di visualizzazione non scrivono dati.

## Diagnostico autorizzato

Lettura produttiva in transazione READ ONLY, nessuna modifica di dati. SHA rilevato: 4a9c0c55ce9319bef89dea97b9a1885c1ace5e66. Nessuna relazione orfana o proprietario discordante nelle verifiche eseguite; nessun gruppo duplicato per nome, senza dedurne assenza universale di duplicati. Sette vincoli FK rilevati. Tra le strutture attive, classificazione mancante negli ID 10 e 11; ID 9 e 12 coerenti. Nessuna classe inventata e nessuna riconciliazione produttiva. Il precedente elenco includeva ID 8, ora non attivo. Il dettaglio minimizzato resta locale privato e non è versionato.

## Matrice campo per campo

S = Struttura; P = Proprietario; U = User. Ogni riga indica la fonte persistente ufficiale. I campi personali dell'account non sostituiscono i dati commerciali di S/P. Gli snapshot di documenti già emessi restano storici.

| Entità | Campo | Fonte | Consultazione e consumatori | Alta e modifica |
|---|---|---|---|---|
| S | `proprietario_id` | struttura.proprietario_id | Gerarchia, servizio, licenze e pagamenti | Flusso amministrativo esistente; niente modifica dal titolare |
| S | `logo` | struttura.logo | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `nome_struttura` | struttura.nome_struttura | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `cir` | struttura.cir | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `tipologia_generale` | FK corrispondente; testo di compatibilità esplicita | Pannelli e Tassa; conflitti segnalati, niente default | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `tipologia_struttura` | FK corrispondente; testo di compatibilità esplicita | Pannelli e Tassa; conflitti segnalati, niente default | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `classificazione` | FK corrispondente; testo di compatibilità esplicita | Pannelli e Tassa; conflitti segnalati, niente default | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `tipologia_generale_id` | struttura.tipologia_generale_id | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `tipologia_struttura_id` | struttura.tipologia_struttura_id | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `classificazione_id` | struttura.classificazione_id | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `tipo_apertura` | struttura.tipo_apertura | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `data_apertura` | struttura.data_apertura | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `data_chiusura` | struttura.data_chiusura | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `nazione` | struttura.nazione | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `regione` | struttura.regione | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `provincia` | struttura.provincia | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `citta` | struttura.citta | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `città` | Alias compatibile del campo senza accento | Consumatori legacy; discordanza segnalata | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `logo_citta` | struttura.logo_citta | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `logo_città` | Alias compatibile del campo senza accento | Consumatori legacy; discordanza segnalata | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `zona` | struttura.zona | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `localita` | struttura.localita | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `località` | Alias compatibile del campo senza accento | Consumatori legacy; discordanza segnalata | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `indirizzo` | struttura.indirizzo | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `numero_civico` | struttura.numero_civico | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `cap` | struttura.cap | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `latitudine` | struttura.latitudine | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `longitudine` | struttura.longitudine | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `ragione_sociale` | struttura.ragione_sociale | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `partita_iva` | struttura.partita_iva | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `codice_fiscale` | struttura.codice_fiscale | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `cin` | struttura.cin | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `codice_unico` | struttura.codice_unico | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `camere_disponibili` | struttura.camere_disponibili | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `letti_disponibili` | struttura.letti_disponibili | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `letti_agg` | struttura.letti_agg | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `istat_username` | struttura.istat_username | Canale operativo specifico; segreti esclusi dalle viste generiche | Controlli specifici del canale, nessuna apertura ai maestri |
| S | `istat_password` | struttura.istat_password | Canale operativo specifico; segreti esclusi dalle viste generiche | Controlli specifici del canale, nessuna apertura ai maestri |
| S | `istat_codice_struttura` | struttura.istat_codice_struttura | Canale operativo specifico; segreti esclusi dalle viste generiche | Controlli specifici del canale, nessuna apertura ai maestri |
| S | `istat_ws_url` | struttura.istat_ws_url | Canale operativo specifico; segreti esclusi dalle viste generiche | Controlli specifici del canale, nessuna apertura ai maestri |
| S | `questura_username` | struttura.questura_username | Configurazione operativa; segreti non esposti | Titolare/gestore autorizzato; Admin / Superadmin |
| S | `questura_password` | struttura.questura_password | Configurazione operativa; segreti non esposti | Titolare/gestore autorizzato; Admin / Superadmin |
| S | `questura_wskey` | struttura.questura_wskey | Configurazione operativa; segreti non esposti | Titolare/gestore autorizzato; Admin / Superadmin |
| S | `questura_codici` | struttura.questura_codici | Canale operativo specifico; segreti esclusi dalle viste generiche | Controlli specifici del canale, nessuna apertura ai maestri |
| S | `questura_puk` | struttura.questura_puk | Canale operativo specifico; segreti esclusi dalle viste generiche | Controlli specifici del canale, nessuna apertura ai maestri |
| S | `questura_ws_simulazione` | struttura.questura_ws_simulazione | Canale operativo specifico; segreti esclusi dalle viste generiche | Controlli specifici del canale, nessuna apertura ai maestri |
| S | `camere_reali_enabled` | struttura.camere_reali_enabled | Configurazione operativa; segreti non esposti | Titolare/gestore autorizzato; Admin / Superadmin |
| S | `attiva` | struttura.attiva | Gerarchia, servizio, licenze e pagamenti | Flusso amministrativo esistente; niente modifica dal titolare |
| S | `avviso` | struttura.avviso | Gerarchia, servizio, licenze e pagamenti | Flusso amministrativo esistente; niente modifica dal titolare |
| S | `messaggio_offline` | struttura.messaggio_offline | Gerarchia, servizio, licenze e pagamenti | Flusso amministrativo esistente; niente modifica dal titolare |
| S | `messaggio_avviso` | struttura.messaggio_avviso | Gerarchia, servizio, licenze e pagamenti | Flusso amministrativo esistente; niente modifica dal titolare |
| S | `scadenza_servizio` | struttura.scadenza_servizio | Gerarchia, servizio, licenze e pagamenti | Flusso amministrativo esistente; niente modifica dal titolare |
| S | `piano` | struttura.piano | Gerarchia, servizio, licenze e pagamenti | Flusso amministrativo esistente; niente modifica dal titolare |
| S | `stato_pagamento` | struttura.stato_pagamento | Gerarchia, servizio, licenze e pagamenti | Flusso amministrativo esistente; niente modifica dal titolare |
| S | `numero_ricevuta_pagamento` | struttura.numero_ricevuta_pagamento | Gerarchia, servizio, licenze e pagamenti | Flusso amministrativo esistente; niente modifica dal titolare |
| S | `telefono` | struttura.telefono | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `telefono_secondario` | struttura.telefono_secondario | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `fax` | struttura.fax | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `email` | struttura.email | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| S | `sito_web` | struttura.sito_web | Pannelli autorizzati; identità operazioni nuove, Web Check-in, Schedine, Questura/ISTAT, Tassa secondo il campo | Alta titolare; modifica Admin assegnato / Superadmin |
| P | `admin_id` | proprietari.admin_id | Gestione amministrativa riservata | Admin assegnato / Superadmin |
| P | `nome` | proprietari.nome | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `email` | proprietari.email | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `telefono` | proprietari.telefono | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `cellulare` | proprietari.cellulare | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `ragione_sociale` | proprietari.ragione_sociale | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `codice_fiscale` | proprietari.codice_fiscale | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `partita_iva` | proprietari.partita_iva | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `codice_destinatario` | proprietari.codice_destinatario | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `codice_unico` | proprietari.codice_unico | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `pec` | proprietari.pec | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `indirizzo` | proprietari.indirizzo | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `numero_civico` | proprietari.numero_civico | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `cap` | proprietari.cap | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `citta` | proprietari.citta | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `provincia` | proprietari.provincia | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `regione` | proprietari.regione | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `nazione` | proprietari.nazione | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `geo_manual` | proprietari.geo_manual | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `latitudine` | proprietari.latitudine | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `longitudine` | proprietari.longitudine | Admin assegnato / Superadmin; titolare consulta dati pertinenti del proprio proprietario | Admin assegnato / Superadmin |
| P | `attivo` | proprietari.attivo | Gestione amministrativa riservata | Admin assegnato / Superadmin |
| P | `note` | proprietari.note | Gestione amministrativa riservata | Admin assegnato / Superadmin |
| P | `note_amministrative` | proprietari.note_amministrative | Gestione amministrativa riservata | Admin assegnato / Superadmin |
| U | `name` | users.name | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `email` | users.email | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `password` | users.password | Autenticazione; hash mai esposto | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `avatar` | users.avatar | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `username` | users.username | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `display_name` | users.display_name | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `telefono` | users.telefono | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `qualifica` | users.qualifica | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `ragione_sociale` | users.ragione_sociale | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `codice_fiscale` | users.codice_fiscale | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `partita_iva` | users.partita_iva | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `codice_destinatario` | users.codice_destinatario | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `pec` | users.pec | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `indirizzo` | users.indirizzo | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `numero_civico` | users.numero_civico | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `cap` | users.cap | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `citta` | users.citta | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `provincia` | users.provincia | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `regione` | users.regione | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `nazione` | users.nazione | Account e autenticazione; profilo personale; API con whitelist | Profilo personale nei soli campi consentiti; gestione account secondo ruolo |
| U | `ruolo` | users.ruolo | Membership e controllo accesso | Solo flusso account autorizzato; vietati nel profilo e nei maestri |
| U | `ruolo_operativo` | users.ruolo_operativo | Membership e controllo accesso | Solo flusso account autorizzato; vietati nel profilo e nei maestri |
| U | `struttura_id` | users.struttura_id | Membership e controllo accesso | Solo flusso account autorizzato; vietati nel profilo e nei maestri |
| U | `proprietario_id` | users.proprietario_id | Membership e controllo accesso | Solo flusso account autorizzato; vietati nel profilo e nei maestri |
| U | `attivo` | users.attivo | Membership e controllo accesso | Solo flusso account autorizzato; vietati nel profilo e nei maestri |
| P | `id` | proprietari.id | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |
| P | `created_at` | proprietari.created_at | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |
| P | `updated_at` | proprietari.updated_at | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |
| P | `deleted_at` | proprietari.deleted_at | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |
| S | `id` | struttura.id | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |
| S | `istat_ws_simulazione` | struttura.istat_ws_simulazione | Configurazione o metadato del modulo dedicato; nessuna copia per ruolo | Regole del modulo esistente; escluso dal payload dei maestri ove non previsto |
| S | `created_at` | struttura.created_at | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |
| S | `updated_at` | struttura.updated_at | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |
| S | `deleted_at` | struttura.deleted_at | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |
| S | `gruppi_inizializzati_at` | struttura.gruppi_inizializzati_at | Configurazione o metadato del modulo dedicato; nessuna copia per ruolo | Regole del modulo esistente; escluso dal payload dei maestri ove non previsto |
| S | `titoli_inizializzati_at` | struttura.titoli_inizializzati_at | Configurazione o metadato del modulo dedicato; nessuna copia per ruolo | Regole del modulo esistente; escluso dal payload dei maestri ove non previsto |
| S | `indirizzi_inizializzati_at` | struttura.indirizzi_inizializzati_at | Configurazione o metadato del modulo dedicato; nessuna copia per ruolo | Regole del modulo esistente; escluso dal payload dei maestri ove non previsto |
| S | `web_checkin_colore` | struttura.web_checkin_colore | Configurazione Web Check-in; consumatore dedicato | Flusso operativo Web Check-in esistente, distinto dalla scheda maestra |
| U | `id` | users.id | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |
| U | `email_verified_at` | users.email_verified_at | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `ultimo_accesso_at` | users.ultimo_accesso_at | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `ultima_uscita_at` | users.ultima_uscita_at | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `ultimo_accesso_ip` | users.ultimo_accesso_ip | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `ultimo_logout_ip` | users.ultimo_logout_ip | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `codice_unico` | users.codice_unico | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `geo_manual` | users.geo_manual | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `latitudine` | users.latitudine | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `longitudine` | users.longitudine | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `google_maps_url` | users.google_maps_url | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `compenso_servizio` | users.compenso_servizio | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `note_servizio` | users.note_servizio | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `note_amministrative` | users.note_amministrative | Profilo, amministrazione account o tracciamento accessi; non fonte commerciale di struttura/proprietario | Flusso account dedicato; note e compensi riservati, esclusi dalla API generica |
| U | `remember_token` | users.remember_token | Autenticazione o canale specifico; escluso dalla consultazione generica | Flusso dedicato autorizzato, mai campo maestro liberamente assegnabile |
| U | `created_at` | users.created_at | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |
| U | `updated_at` | users.updated_at | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |
| U | `deleted_at` | users.deleted_at | Identità canonica e ciclo di vita; relazioni per ID | Generati o gestiti dal flusso applicativo; non editabili nei maestri |

## Evidenze e limiti

R1: otto regressioni riprodotte sulla baseline. R5: nove fallimenti Web preesistenti confermati sul codice originale; test fiscali aggiornati al contratto della PR precedente e controllo tenant del parent corretto. R9: 107 test / 809 verifiche e tre browser PASS. R10: quattro browser PASS, desktop 1440, tablet 768, mobile 390 e alta titolare. R11: 112 test / 836 verifiche PASS; quattro browser finali PASS (1440/768/390 e alta titolare), 15 guardie pure PASS. Tutte le prove Laravel nel launcher ufficiale isolato, PHP 8.3.36, MariaDB e filesystem effimeri, fixture sintetiche; nessuna trasmissione reale. Manifest SHA e log locali conservati sotto output/anagrafiche-unificate-2026-10-23 nel checkout originario; i NONPASS sono conservati.

Nessuna migrazione storica modificata, nessuna DDL o riconciliazione reale. Nessuna modifica al componente GEO immutabile. Aliquote e formati fiscali non modificati. Aggiornamenti di account e anagrafica raggruppati in transazioni; trasferimento proprietario disattiva e scollega tutti gli account struttura precedenti. I documenti storici non vengono rigenerati.

## Pubblicazione

[PR #16 integrata](https://github.com/joomlu/Schedinedinotifica/pull/16): codice `ec2d9784b5dd93148f800fe912e89afb0b529104`, fixture/evidenze `b82bc9b0b234aa50dba083452c607dc6995a619d`, merge `1b0f877a14e5e65f3bef0ff7dc327c53d86b9dc9`. Candidato non pubblicato; SHA effettivamente online `4a9c0c55ce9319bef89dea97b9a1885c1ace5e66`. Il diagnostico non certifica accettazione StayTour. Le classificazioni mancanti richiedono un dato certo del gestore. Deduplicazione autorizzata PASS: 99.688 file, nessuna eliminazione, byte/inode/metadati invariati; liberi da 1.985.691.648 a 5.778.370.560 byte. Necessario ripetere il gate residuo dopo PREPARE.

R14 consumatori aggiuntivi: 76 test / 742 verifiche PASS (loghi, anteprima/configurazione Tassa, Clienti, Arrivi e ciclo ISTAT simulato). R12 correttamente fermato dalla guardia SHA dopo il commit; sincronizzatore allineato agli oggetti del commit senza alterare la guardia. R13 ha rilevato una fixture ISTAT senza selezione esplicita della struttura; R14 rende esplicita la sessione e conserva tutte le attese del canale. Nessun nuovo cambiamento applicativo. PR #16, integrazione espressamente autorizzata dall’utente dopo il primo rifiuto automatico; deploy sempre subordinato a C5.


## Consuntivo C5 e stato sicuro

Due PREPARE freschi, ciascuno con nonce/backup/prebuild nuovi. Kit e preflight PASS, nove sonde PASS, nessuna migrazione prevista. Entrambi i trasferimenti sono scaduti nel limite globale originale di 120 secondi: primo DB 23.342.848 byte in 10,545 secondi e filesystem parziale 392.364.032 byte in 109,632 secondi; secondo DB 23.342.848 byte in 3,557 secondi e filesystem parziale 354.484.224 byte in 116,531 secondi. Il filesystem atteso era circa 454,7 MB: i parziali non sono backup recuperabili attestati. Nessuna modifica ai limiti, nessun riuso del parziale come prova e nessun RUN avviato.

STOP sicuro verificato successivamente sul server: SHA precedente `4a9c0c55ce9319bef89dea97b9a1885c1ace5e66`, Git pulito, FPM attivo, nove sonde positive, barriere di manutenzione assenti, chiavi e ambiente preservati, 66 tabelle. Questura/ISTAT disabilitati e trasporto mail generale `array` preservati. Nessuna fixture online creata; nessuna trasmissione o modifica di dati reali. I materiali locali del QA sono preparati ma non eseguiti. I backup sorgente e i trasferimenti parziali sono conservati, senza cancellazioni. La VM viene restituita spenta come all'inizio; l'ambiente quotidiano è preservato.

La deduplicazione autorizzata ha verificato byte, inode, proprietario/gruppo, permessi, mtime/ctime, numero link e attributi estesi; l'atime non fa parte del sigillo. Nessun file rimosso, SQL/cifrati/chiavi/.env/upload/loghi esclusi.

Il primo rifiuto automatico del merge è stato superato solo dopo consenso esplicito dell'utente; il merge è avvenuto tramite GitHub. Non rimane un blocco di autorizzazione: il blocco attuale è il trasferimento entro i limiti C5. Per riprendere serve un trasferimento completo nel limite vigente, poi recupero indipendente, custodia Extra, controllo capacità residua, RUN/S0, monitor e QA online; nessuna fase può essere promossa a PASS anticipatamente.

## Comandi ed evidenze riproducibili

I comandi seguenti sono stati eseguiti dal checkout originario, tramite l'adattatore locale che invoca esclusivamente il launcher ufficiale nel namespace VM. Non autorizzano test Laravel sull'host.

```text
python3 output/anagrafiche-unificate-2026-10-23/collaudo.py R11-finale --phpunit tests/Feature/AnagraficheUnificateTest.php tests/Feature/ProprietarioAccessoValidationTest.php tests/Feature/StrutturaAuthorizationTest.php tests/Feature/StrutturaSelezioneTest.php tests/Feature/AccountSessionSecurityTest.php tests/Feature/TassaBellariaVersionamentoTest.php tests/Feature/TassaBellariaEtaCsvTest.php tests/Feature/TassaPeriodoComunaleTest.php tests/Unit/TassaDiSoggiornoServiceTest.php tests/Feature/WebCheckinFiscalContextTest.php tests/Feature/WebCheckinParentSecurityTest.php tests/Feature/QuesturaCredentialEncryptionTest.php tests/Feature/IstatTransmissionSecurityTest.php --fixtures tests/Isolation/seed-anagrafiche.php --playwright tests/Feature/anagrafiche-unificate.playwright.spec.js
python3 output/anagrafiche-unificate-2026-10-23/collaudo.py R14-consumatori-contesto --phpunit tests/Feature/GeoLogoAtomicitaTest.php tests/Feature/TassaBellariaAnteprimaTest.php tests/Feature/ConfigurazioneTassaSuperadminTest.php tests/Feature/CustomerManagementTest.php tests/Feature/ArriviCustomerReferenceSecurityTest.php tests/Feature/IstatCycleTest.php
limactl shell schedine-sviluppo-interno sudo bash /opt/schedine-sviluppo/strumenti/namespace-selinux-visibile.sh runuser -u collaudo -- env -i HOME=/home/collaudo.guest PATH=/usr/local/bin:/opt/schedine-runner-runtime-Q4WIbkDa/node-v20.18.2-linux-arm64/bin:/usr/bin:/bin LANG=C.UTF-8 sh -c 'cd /home/collaudo.guest/anagrafiche-sfppoupz/tests/Isolation && python3 -m unittest test_database_policy.py test_guards.py'
python3 output/anagrafiche-unificate-2026-10-23/c5/client.py prepare
python3 output/anagrafiche-unificate-2026-10-23/c5/client.py prepare r2
```

R11: 112 test / 836 verifiche e quattro browser PASS. R14: 76 test / 742 verifiche PASS, selezione distinta, non una suite globale. Guardie: 15 PASS; lint statico dei 27 PHP della prima modifica PASS; diff check PASS. Hash dei sorgenti e log nelle ricevute versionate. Tutti i fallimenti intermedi conservati localmente. Test tenant e sicurezza positivi limitatamente ai circuiti provati, non attestazione generale del progetto.

CODICE MODIFICATO: SI; TEST MODIFICATI: SI; DOCUMENTAZIONE MODIFICATA: SI; CONFIGURAZIONE APPLICATIVA MODIFICATA: NO; SCHEMA/DATI REALI MODIFICATI: NO; TEST ESEGUITI: SI; COMMIT: SI; PUSH: SI; DEPLOY: NO; TRASMISSIONI ESTERNE UFFICIALI: NO. Configurazioni amministrative C5 dedicate e depositi di backup creati nell'ambito autorizzato, senza modificare la configurazione applicativa.
