# Tassa — precisazione dei pendenti operativi — 24 ottobre 2026

Stato **PARZIALE; BASELINE IN AUDIT**. Prosecuzione dell’audit sul sistema, separata dal rinnovo del sito pubblico. SHA applicativo produttivo riconfermato in sola lettura: `dda6b24ed40a3257ad5226ec3459a52f149b95fa`. Nessuna modifica applicativa, fiscale, normativa, di schema o di dati reali in questo approfondimento.

## Risultato nuovo

La precedente lettura globale contava una schedina effettiva senza nascita nel periodo giugno–settembre 2026. La nuova verifica del collegamento alla struttura dimostra che appartiene a una struttura **cancellata logicamente**. Non è pertanto attestato un blocco per nascita mancante nelle strutture Bellaria non cancellate. Il dato storico resta conservato: nessun completamento, cancellazione o riattivazione eseguiti.

| Controllo aggregato produttivo | Risultato |
| --- | ---: |
| Classificazioni canoniche mancanti, strutture attive Bellaria | 2 |
| Classificazioni mancanti, strutture attive altri Comuni | 0 |
| Conflitti di classificazione, strutture attive | 0 |
| Titolari senza nascita, soggiorni effettivi in stagione, Bellaria non cancellate | 0 |
| Componenti senza nascita, medesimo perimetro e collegamento struttura/schedina coerente | 0 |
| Nascite mancanti globali, struttura cancellata logicamente | 1 |
| Nascite mancanti globali, struttura assente oppure altri Comuni attuali | 0 |
| Export consolidati reali | 0 |

[Evidenza aggregata generale](evidenze/tassa-prosecuzione-2026-10-24/lettura-aggregata.json) e [delimitazione del perimetro](evidenze/tassa-prosecuzione-2026-10-24/approfondimento-aggregato.json). Nessun identificativo, nome di ospite o credenziale nelle evidenze pubbliche. Il valore SQL `null` nella somma dei motivi di esenzione deriva dall’insieme vuoto dei titolari senza nascita; non indica una persona da classificare.

Le query usano transazioni `READ ONLY`, periodo `arrive < 2026-10-01` e `departure > 2026-06-01`, circuito effettivo (`is_arrive=0`, circuito schedina oppure null). Per i componenti sono verificati entrambi i collegamenti `schedina_id` e `struttura_id`. La città segue il fallback del modello: la prima query di approfondimento è stata respinta perché il DB usa la colonna legacy `città`, non `citta`; letti i metadati, la ripetizione usa la colonna esistente. Nessuno schema modificato e nessuna normalizzazione dei dati reali.

## Completamento canonico ancora necessario

Per le due classificazioni attive mancanti occorre il valore reale documentato, non deducibile da tariffa o nome della struttura. L’operatore autorizzato deve usare la scheda esistente Admin o Superadmin, controllare tipologia generale → tipologia struttura → classificazione compatibile e salvare la selezione canonica. Le route effettive sono `/admin/strutture/{id}/edit` e `/superadmin/strutture/{id}/edit`; gli ID vanno selezionati dall’elenco autorizzato del gestionale, non pubblicati nel rapporto.

Il percorso usa `AnagraficaStruttura` e le relazioni esistenti; Proprietario e utenti operativi consultano i dati ma non acquisiscono nuovi poteri sui campi maestri. Non aggiungere record duplicati o tariffe di ripiego per superare il blocco. Dopo il completamento reale verificare che la stessa classificazione sia mostrata nei Dati struttura e che il controllo Tassa non segnali dati canonici mancanti. La qualifica automatizzata corrente continua a coprire il rifiuto dei dati incompleti e l’isolamento tra strutture.

## Limiti e prossimo passaggio

Restano aperti il completamento verificato delle due classificazioni e l’accettazione esterna del tracciato StayTour. L’utente ha già dichiarato non disponibile una sessione gestore o un validatore ufficiale senza effetti fiscali; nessun invio ufficiale eseguito. La decisione funzionale storica sul compleanno incluso e il codice450 resta distinta da una nuova certificazione municipale.

Questa precisazione supera soltanto l’attribuzione della nascita mancante ai blocchi del perimetro operativo corrente; conserva i precedenti esiti tecnici e non dichiara il modulo chiuso. Nessun nuovo rilascio necessario per questo aggiornamento documentale. Il rinnovo web procede nel proprio worktree, con proprietà dei file e collaudo coordinati separatamente.
