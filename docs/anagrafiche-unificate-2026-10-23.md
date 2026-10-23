# Anagrafiche unificate — verifica del 23 ottobre 2026

Stato: PARZIALE, implementazione verificata in ambiente isolato; pubblicazione e verifica online ancora pendenti. Stato generale del progetto: BASELINE IN AUDIT.

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

## Evidenze e limiti

R1: otto regressioni riprodotte sulla baseline. R5: nove fallimenti Web preesistenti confermati sul codice originale; test fiscali aggiornati al contratto della PR precedente e controllo tenant del parent corretto. R9: 107 test / 809 verifiche e tre browser PASS. R10: quattro browser PASS, desktop 1440, tablet 768, mobile 390 e alta titolare. R11: 112 test / 836 verifiche PASS; quattro browser finali PASS (1440/768/390 e alta titolare), 15 guardie pure PASS. Tutte le prove Laravel nel launcher ufficiale isolato, PHP 8.3.36, MariaDB e filesystem effimeri, fixture sintetiche; nessuna trasmissione reale. Manifest SHA e log locali conservati sotto output/anagrafiche-unificate-2026-10-23 nel checkout originario; i NONPASS sono conservati.

Nessuna migrazione storica modificata, nessuna DDL o riconciliazione reale. Nessuna modifica al componente GEO immutabile. Aliquote e formati fiscali non modificati. Aggiornamenti di account e anagrafica raggruppati in transazioni; trasferimento proprietario disattiva e scollega tutti gli account struttura precedenti. I documenti storici non vengono rigenerati.

## Pubblicazione

Commit, PR, C5 e SHA pubblicato: PENDENTI. Il diagnostico non certifica accettazione StayTour. Le classificazioni mancanti richiedono un dato certo del gestore. Deduplicazione autorizzata PASS: 99.688 file, nessuna eliminazione, byte/inode/metadati invariati; liberi da 1.985.691.648 a 5.778.370.560 byte. Necessario ripetere il gate residuo dopo PREPARE.
