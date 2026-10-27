# Contatti, selettori e WhatsApp — 27 ottobre 2026

Stato: VERIFICATO nelle prove isolate elencate; pubblicazione BLOCCATA prima di PREPARE per capienza. BASELINE IN AUDIT invariata.

## Modifiche e conservazione

I cinque selettori di Contatti usano Select2, già impiegato dal gestionale: motivo, preferenza demo, tipo di struttura, piano e funzione. Menu responsive, etichette accessibili, descrizioni e stato non valido collegati al controllo visibile. La scelta con mouse o tastiera aggiorna gli stessi campi nativi: selezione da CTA/query, ritorno nella cronologia, ripristino, riepilogo e azzeramento restano sincronizzati. Corretto l'allargamento del menu aperto su mobile. Caratteri dei campi mobili a16px per evitare lo zoom involontario.

Collegamento WhatsApp su tutte le pagine pubbliche al numero +39 393 099 1968, con icona locale, nome accessibile, nuova scheda e messaggio generico italiano. Testo su desktop, controllo compatto su mobile e Contatti, aree sicure e spazio nel footer. Il pulsante cerca uno spazio libero nella parte inferiore destra; non copre i controlli o le righe di testo considerate. Se manca spazio si nasconde temporaneamente; menu, conferma e compilazione mobile lasciano liberi i relativi controlli.

Il candidato include la precedente pagina unica Contatti già preparata: tre motivi commerciali, preferenza demo e informazioni della struttura nel CRM esistente; messaggio facoltativo, consenso, CSRF, antispam, transazione, deduplicazione e compatibilità dei vecchi POST conservati. Collegamenti `/contatti#demo`, piani, funzioni e Max Full verificati; tour della demo preservato. Nessuna migration, nuova integrazione o modifica del gestionale. Le altre pagine cambiano soltanto per il collegamento WhatsApp.

## Verifiche

Solo runtime ufficiale isolato: VM Rocky, rete/PID/mount confinati, SELinux Enforcing, database e filesystem effimeri, nessun servizio esterno reale. PHP8.3.36 e MariaDB11.8.9.

- WA-R7 sul candidato finale da GitHub: 21 test PHP /405 asserzioni PASS, build/architettura/GEO e23 rifiuti di override PASS.
- WA-R7 sul codice finale: sei scenari browser PASS. Tre POST sintetici fino alla conferma e alla lettura del Lead/attività CRM a1440/768/390; motivo e preferenza distinti, piano Duo, Web Check-in e Max Full conservati. Messaggio vuoto accettato; dati mancanti non inviati.
- Mouse, Enter, frecce, Escape, Tab/Shift+Tab, focus, cinque Select2, selezione mantenuta, errori accessibili, azzeramento, token conservato e collegamenti storici/query/tour PASS. Menu aperto/chiuso desktop/mobile senza overflow.
- WhatsApp presente nelle pagine pubbliche, etichetta e destinazione esatte, geometria e apertura della nuova scheda PASS nel browser isolato. La rete esterna resta bloccata: nessun messaggio inviato. Verifica HTTP separata: rinvio302 ufficiale a `api.whatsapp.com/send` con numero393930991968 e testo previsto; non certifica consegna o disponibilità dell'account.
- Catture di selettori e WhatsApp desktop/mobile ispezionate. Nessuna prova su un telefono fisico né invio reale in produzione.

WA-R1 conservata: quattro percorsi funzionali PASS, due aspettative sul caricamento esterno fallite per il blocco del trasporto isolato; corretta la sola verifica del tentativo di apertura. WA-R2: stessi quattro percorsi PASS, due rilievi di visibilità sul posizionamento del pulsante; corretta la ricerca dello spazio. WA-R3 e WA-R4: sei browser PASS; ulteriore protezione del testo qualificata in WA-R7. WA-R5: cinque browser PASS, un controllo mobile NONPASS perché la scatola del paragrafo includeva spazio vuoto; corretto il test usando le righe di testo effettivamente disegnate, nessuna modifica applicativa. WA-R6 sul medesimo applicativo ha corretto la misura del test; WA-R7 include anche la verifica che la scelta facoltativa «Da valutare insieme» resti selezionabile. Esecuzioni sovrapposte non sommate. Nessuna suite globale certificata.

Comandi selezionati, passati all'avviatore ufficiale nella VM:

```text
--phpunit tests/Feature/CommercialContactTest.php tests/Feature/PublicSiteCompletionTest.php --fixtures tests/Isolation/commercial-contact-fixtures.php --playwright tests/Feature/commercial-contact.playwright.spec.js
--fixtures tests/Isolation/commercial-contact-fixtures.php --playwright tests/Feature/commercial-contact.playwright.spec.js
```

[Impronte dei sorgenti e qualifica](evidenze/selettori-whatsapp-2026-10-27/verifica.json).

## Git e rilascio

Copia candidata separata da GitHub, branca `codex/contatti-selettori-whatsapp-20261027`; checkout operativo e lavori estranei preservati. Base produttiva rilevata e4f6682; main remoto fdd4884. Differenze estranee del checkout operativo escluse dal candidato e qualifica ripetuta sulla copia da GitHub.

La pubblicazione applicativa richiede nuovo PREPARE/S0, backup cifrati e recupero indipendente C5, preflight corrente, verifiche online e monitor. Nessun checkpoint precedente riutilizzabile. Stato finale di commit e pubblicazione da registrare dopo l'esecuzione; nessuna pubblicazione attestata da questo rapporto preliminare. Nessun dato reale, messaggio, email o trasmissione agli enti durante le prove. Rischio MEDIO limitato al flusso commerciale; autorizzazioni e tenancy del gestionale invariate, nessuna nuova qualifica globale.

## Consuntivo Git e blocco prima di PREPARE

Correzioni caricate nel commit **c54b705003e59d5525b4c0c8413a9b94d907decf**, [PR42](https://github.com/joomlu/Schedinedinotifica/pull/42) aperta. Nessun controllo CI riportato da GitHub sul candidato; le prove indicate sono quelle del runtime isolato, non una revisione indipendente. Nessun merge o deploy eseguito.

Il preflight della capacità è BLOCCATO: dopo le pulizie circoscritte, misura Mac6.983.827.456 byte (circa6,5GiB), inferiore alla riserva8GiB; servono inoltre spazio per i nuovi backup e recuperi. VM3.670.802.432 byte liberi ed Extra479.754.235.904; nessuna soglia o budget ridotti. Per riprendere è richiesto spazio aggiuntivo sul Mac e un nuovo preflight completo; raccomandati almeno altri5GiB, da rimisurare prima del rilascio. Il riferimento di capacità impone di fermarsi prima di PREPARE/RUN quando lo spazio non basta. Nessun PREPARE, S0, manutenzione o modifica produttiva avviati. Il kit C5 è soltanto una preparazione locale da riconciliare con il futuro candidato approvato, non un rilascio.

Ritirate esclusivamente le due copie temporanee di vendor/node_modules della task dopo confronto positivo di34.131 file/link e assenza di utilizzatori; originali quotidiani invariati. Trim della sola VM su blocchi già liberi, senza eliminazione di file. Sei cartelle temporanee di rilettura C5 concluse PASS ritirate dopo confronto SHA-256 con i cifrati originali e verifica di assenza utilizzatori; originali, backup interni/Extra, chiavi, SQL, manifest e ricevute conservati.1.803.563.008 byte di copie logiche ritirate non equivalgono a spazio fisico recuperato. Misure effettive rivalutate dopo il ritiro; minimo VM campionato durante il collaudo2.234.630.144 byte, sopra la riserva1GiB.

Codice e test modificati SÌ; documentazione e Maestro aggiornati SÌ; configurazione/schema/dati reali modificati NO; prove isolate/commit/push SÌ; merge/deploy/invii commerciali o agli enti NO. Pubblicazione e verifica delle nuove funzioni online PENDENTI. Nessuna validazione globale del progetto: BASELINE IN AUDIT invariata.

Rilettura finale della produzione: nove pagine HTTP200, Git pulito su e4f6682cbcddc034563d9e6daa59025883670ede e versione51ed34b9a756dbaa0714b6f7; le correzioni e il nuovo WhatsApp NON sono online. 8706 file estranei presenti all’inizio sono rimasti byte-identici. HEAD operativo main/a6d40647 e staging invariati.
