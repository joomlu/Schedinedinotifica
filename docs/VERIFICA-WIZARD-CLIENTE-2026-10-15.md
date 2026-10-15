# Cliente — procedura guidata, 15 ottobre 2026

**PARZIALE: correzioni e verifica server isolata completate; formulario verificato in isolamento; autorizzazione operativa e rilascio pendenti. BASELINE IN AUDIT generale invariata.** Scope autorizzato: diagnosi, correzioni del wizard, prove isolate/VM/Chromium, Maestro e registro esistente, commit, push e pubblicazione C5. Nessuna trasmissione o cliente sintetico in produzione. HEAD iniziale locale e GitHub verificato `7312f84d028872bc228c2c26e6f9d1256e1b4beb`; produzione stessa SHA, Git pulito e manutenzione assente. Lavoro documentale preesistente conservato.

## Significato dei comandi

- **Anagrafica** in basso è un pulsante `type="button"` che attiva la seconda scheda, dopo la verifica della scheda corrente. Non persiste dati; la scheda omonima contiene nascita, cittadinanza e documento. Percorso mantenuto.
- **Salva voce**: chiarimento umano del 15 ottobre: significa **Salva a bozza**. Nessuna route o entità autonoma «Voce». La bozza salva un cliente della classificazione esplicita e riapre `customer.edit`; non crea una classificazione alternativa. La completezza viene calcolata dai dati presenti, senza un nuovo stato dedotto.
- **Salva a cliente**: creazione o modifica, poi elenco `/clienti`.
- **Salva a schedina**: salva il solo cliente e apre `newschedina?customer_id=...`; nessuna schedina persistita o trasmessa da questo comando. Richiesta e Componente respinti; il passaggio a Ospite richiede una scelta esplicita.

## Matrice di obbligatorietà

| Dato / azione | Richiesta | Ospite definitivo / nuova schedina | Componente definitivo |
| --- | --- | --- | --- |
| Tipo cliente del catalogo attivo, nome, cognome | Obbligatori | Obbligatori | Obbligatori |
| Email oppure telefono oppure cellulare propri | Almeno uno; fax e azienda esclusi | Facoltativi | Facoltativi |
| Sesso, Stato di nascita, cittadinanza, data di nascita | Facoltativi, validati se compilati | Obbligatori | Obbligatori |
| Comune e provincia di nascita italiani | Facoltativi, coerenti se compilati | Obbligatori se Italia | Obbligatori se Italia |
| Città estera di nascita | Facoltativa | Facoltativa: il tracciato richiede lo Stato | Facoltativa: il tracciato richiede lo Stato |
| Tipo e numero documento, Stato rilascio | Facoltativi, validati se compilati | Obbligatori | Facoltativi, validati se compilati |
| Comune rilascio italiano | Facoltativo | Obbligatorio se rilascio in Italia | Facoltativo |
| Residenza, indirizzo, CAP, regione, tipo via, civico | Facoltativi, coerenti se compilati | Facoltativi, coerenti se compilati | Facoltativi, coerenti se compilati |
| Date rilascio/scadenza, autorità | Facoltative, valide se compilate | Facoltative, valide se compilate | Facoltative, valide se compilate |
| Azienda e contatti azienda | Facoltativi; non sostituiscono il contatto proprio | Facoltativi | Facoltativi |
| Privacy, Marketing, Comunicazioni | Facoltativi e indipendenti | Facoltativi e indipendenti | Facoltativi e indipendenti |
| Salva a bozza | Minimi Richiesta invariati | Tipo/nome/cognome e validità dati informati; percorso incompleto preesistente conservato | Come Ospite a bozza |
| Salva a schedina | Rifiutato, senza promozione | Consentito con minimi principali | Rifiutato, senza promozione |

Fondamento: [manuale ufficiale Alloggiati Web](https://alloggiatiweb.poliziadistato.it/portalealloggiati/Download/Manuali/MANUALEALBERGHI.pdf), consultato il 15 ottobre 2026, pp.7–13 e tracciati pp.31–34; confronto con `QuesturaTxtExportService`, che richiede il documento per il principale e distingue `17→19` famiglia e `18→20` gruppo. Tipo cliente OSPITE/COMPONENTE/RICHIESTA resta distinto dai cinque Tipi alloggiato Questura. Periodo, arrivo, permanenza e relazione si definiscono nella schedina; non sono inventati nel cliente. Codici, mapping, cataloghi e trasmissione invariati.

## Correzioni e conservazione

Contatto alternativo Richiesta e rifiuto dei numeri senza cifre; validazione anche a bozza, forme non scalari e modalità manipolate; niente sanificazione silenziosa del nome sul server. Minimi principali/componenti separati, niente obblighi di residenza/documento aggiuntivi estranei al tracciato. Obblighi, indicatori e focus aggiornati nell’interfaccia; messaggi accanto ai campi; pulsanti a capo sul mobile.

Validazione dei riferimenti GEO originali prima della conversione canonica nel contratto testuale esistente: identificatori esistenti, Paese, regione/provincia/comune, CAP, nascita e documento; estero conserva i testi liberi previsti dal componente ufficiale. Voci private limitate alla struttura e allo stato attivo; valori legacy già presenti conservabili senza attribuzioni arbitrarie. Il componente riceve ID effettivi e modalità Italia/Estero coerente: il precedente passaggio di sole etichette e flag legacy poteva imporre la provincia alla Francia. Nessuna deduzione della cittadinanza dal luogo di nascita; sui legacy senza nazione non viene precompilata Italia. Nessun nuovo catalogo o modifica del componente GEO ufficiale. Parametri ausiliari GEO non scalari respinti. Numerazione cliente non accetta più un valore imposto dal payload e prosegue correttamente oltre 9999, senza rinumerare i record esistenti. Tipo via facoltativo con opzione vuota, soltanto voci attive per le nuove selezioni; storici già utilizzati mantenuti. Indirizzo, civico e tipo via azienda, presenti nel modello ma assenti dal formulario, ora conservabili e modificabili.

Accesso tramite il middleware ufficiale `RequireAuthorizedStruttura`, senza estendere l’eccezione operativa Admin. Lettura e modifica di ID estranei respinte nei quattro ruoli; struttura del payload ignorata in creazione. Creazione e numerazione in una transazione con lock della struttura; aggiornamento autorizzato prima della validazione dei dati. Due nuove colonne nullable e chiave univoca per riconoscere durevolmente il reinvio dello stesso formulario; digest senza dati personali in chiaro. Una seconda richiesta identica restituisce il cliente esistente; contenuto diverso con lo stesso identificatore richiede di riaprire il cliente. Nessuna deduplicazione per somiglianza di persone o riclassificazione legacy. La pulizia del salvataggio locale riguarda soltanto la bozza effettivamente salvata. Riferimenti legacy non mappabili conservati, ma bloccanti per il trasferimento a una nuova schedina fino a correzione esplicita. Migrazione `2026_10_15_100000_add_wizard_submission_keys_to_clienti`: sola estensione, nessun backfill.

Consensi non selezionati automaticamente; timestamp registrati dal server e preservati se la scelta resta attiva, senza fidarsi del timestamp inviato. Marketing e Comunicazioni restano indipendenti. Export email marketing filtra `marketing_consent`; lista WhatsApp filtra `communication_consent`, come prima. I nomi e testi legali esistenti non sono riscritti: la qualificazione giuridica della voce «Privacy» non viene dedotta da un booleano. Formulario conserva i dati dopo errori; disattivazione/riattivazione azienda non cancella immediatamente i valori digitati.

## Evidenze effettive e limiti

Solo runtime ufficiale nella VM `schedine-sviluppo-interno`, namespace privo di rete esterna, database/storage/processi effimeri e fixture sintetiche. Nessun test Laravel su Herd, database quotidiano o produzione. Driver e log privati: `output/verifica-wizard-cliente-2026-10-15/`.

- Riproduzione iniziale: 11 test, 17 asserzioni, 5 fallimenti; primo avvio arrestato al controllo SHA per cache Python. Ripetizione con cache propria, senza indebolire guardrail.
- Prima correzione: 11 test / 37 asserzioni PASS.
- Matrice estesa precedente alla migrazione durevole: 69 test / 468 asserzioni PASS. Suite definitiva: **80 test / 532 asserzioni PASS**, incluse migrazione additiva/idempotenza e conservazione, reinvio dopo svuotamento cache, guasto fra le scritture, progressivo oltre 9999, quattro ruoli/CSRF/ID estranei, minimi, GEO, filtri export e timestamp manipolati. Build/GEO e 23 rifiuti override PASS.
- Fallimenti preparatori conservati: chiave iniziale di reinvio legata alla sessione; fixture pregresse senza cataloghi; prime attese browser e fixture Richiesta prive del contatto; fixture della prova DDL non ripulita prima delle suite successive. Nessun fallimento viene promosso a PASS.
- **15 guardie PASS**, build/GEO immutabile e **23 override rifiutati PASS**. Chromium mobile dedicato: **1 prova PASS / 5,2 s**, quattro schede tutte 390/390, tipo/cittadinanza/nazione legacy vuoti conservati. **6 percorsi funzionali Chromium PASS / 1,6 minuti**: contatti Richiesta e riapertura desktop/mobile, Componente definitivo senza documento, Ospite verso nuova schedina, cataloghi/ID estranei e bozza delle tre classificazioni con conferma/annullamento. La prova mobile aggiunge Enter/freccia destra fra le schede, Esc sulla conferma e zero scritture al cliente dopo annullamento. Totale Chromium: **7 prove PASS**.
- **VM quotidiana: migrazione NON eseguita**. La revisione automatica ha respinto l’operazione DDL persistente per autorizzazione ritenuta non abbastanza esplicita nella richiesta allegata; chiesta conferma specifica all’utente. Nessun aggiramento o scrittura sul DB quotidiano. Chiusura: approvazione umana esplicita, checkpoint cifrato riletto, confronto di tutte le colonne originali, ripetizione senza modifiche e riavvio del servizio.
- C5, monitor e allineamento operativo finale: **PENDENTI**. Chrome produttivo apre il login; accesso autenticato richiesto all’utente, senza comunicazione di password.

Comandi di verifica effettivi (driver privati con invocazione completa del runtime ufficiale, namespace senza rete esterna e cache Python propria):

```sh
python3 -B output/verifica-wizard-cliente-2026-10-15/test.py
python3 -B output/verifica-wizard-cliente-2026-10-15/browser.py
python3 -B output/verifica-wizard-cliente-2026-10-15/layout.py
python3 -B output/verifica-wizard-cliente-2026-10-15/guardie.py
```

La selezione PHPUnit è `CustomerWizardMigrationTest`, `CustomerWizardTest`, `TipoClienteSistemaTest`, `SchedinaStoreTest`, `SchedinaCustomerReferenceSecurityTest`, `TopbarRenderingTest`. Nessun risultato di questo ambiente viene attribuito alla produzione. La sintassi PHP è controllata senza bootstrap applicativo; nessuna prova Laravel su Herd. Le inizializzazioni e le DDL distruttive della sola fixture diagnostica avvengono esclusivamente nel database effimero attestato.

## Chiusura

Commit/push in preparazione selettiva; rilascio e ricevuta conclusiva ancora PENDENTI. [Evidenza non sensibile e digest](evidenze/wizard-cliente-2026-10-15/verifica.json). Nessuna DDL sul quotidiano o sulla produzione è stata eseguita da questa attività. Nessuna attestazione generale di completamento progetto. I pendenti restano nel registro esistente; la ricevuta esterna deve contenere SHA locale/GitHub/produzione/VM, migrazioni, conservazione e stato concluso del guardiano. Nessun commit autoreferenziale dopo la ricevuta.
