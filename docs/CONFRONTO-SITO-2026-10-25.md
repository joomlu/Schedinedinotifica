# Confronto del sito sostituito, pubblicato e candidato — 25 ottobre 2026

**Analisi dei sorgenti e implementazione frontend; nuova qualifica e pubblicazione da eseguire. BASELINE IN AUDIT invariata.** Questo documento non estende i PASS storici al nuovo candidato.

## Quale versione è stata sostituita

Il riferimento precedente è `a90d63aa6bd85f668d1ba61a4a5d776e5529cb98`, SHA effettivamente servito prima del rilascio del sito, attestato dai preflight/C5 nel rapporto [pubblicazione precedente](PUBBLICAZIONE-SITO-2026-10-25.md). Il confronto è stato eseguito con `git show a90d63aa:web/<pagina>.html`, non scegliendo una versione visivamente più favorevole. La versione pubblicata successiva è `1edf049550e624d393d4d8278f3ea541b89dcdf6`; il nuovo worktree parte da main `1a2e5e1f616d2e5a236861be6efe1a6c1c61df88`, consuntivo documentale dello stesso applicativo.

Nelle colonne «finale» si descrive il candidato implementato, non un rilascio già eseguito. Checkout originario, worktree dell’anteprima e worktree del rilascio precedente restano separati.

## Matrice pagina per pagina

| Area | Precedente a90d63aa | Pubblicato 1edf0495 | Risultato finale candidato |
|---|---|---|---|
| Home `/` | Panoramica clienti/schedine/Web Check-in/adempimenti/organizzazione; diversi testi spiegavano al venditore come presentare il prodotto | Identità Tango Soft Pro, fotografia, screenshot autentico, piani e CTA demo | Design preservato; destinatari e natura del software espliciti, collegamento alla pagina ampliamenti dedicata |
| Funzionalità `/moduli` | Sei macro-moduli: clienti, schedine e recuperi, Web Check-in, Questura, Tassa/statistiche, organizzazione | Nove sezioni con esempi e requisiti; import/stampe e strutture raccolti in sezioni più ampie | Dettaglio esplicito proprietari/multistruttura, individuali/famiglie/gruppi, modelli e comunicazioni, Tabella A distinta dal tracciato regionale; CTA con interesse pertinente |
| Moduli e ampliamenti | Nessuna pagina autonoma | Voce menu diretta a `/moduli#stato-funzionalita` | Nuova `/ampliamenti`: sette percorsi verificati e Max Full, con benefici, esempio, disponibilità, requisiti e richiesta contestuale. Ancora legacy preservata su `/moduli` |
| Demo `/demo` | Percorso editoriale e immagini; testi rivolti a chi doveva preparare una demo/video, senza video operativo | Tour interattivo di cinque schermate autentiche e dati dimostrativi; tastiera, precedente/successivo | Tour preservato; navigazione coerente con la sesta pagina, verifiche da ripetere anche con accesso diretto, reload e storia browser |
| Prezzi `/prezzi` | Basic 200€/anno, Professional Pro 340€/anno, Pro/Enterprise su richiesta; criteri e riferimenti testuali non omogenei (es. «piano Start») | Catalogo approvato unico:50/100/200 appartamenti,320 complessivi per1/2 strutture,+IVA annuali, preventivi oltre soglie | Stesse tariffe e condizioni, nessuna modifica del catalogo prezzi; nessun ripristino delle offerte storiche |
| Max Full | Extra consigliati come configurazione/migrazione/formazione/supporto; non stessa offerta finale | Servizio aggiuntivo, sette attività, prezzo e condizioni personalizzati | Dettaglio principale `/prezzi#max-full` preservato e richiamato anche da `/ampliamenti#max-full` |
| Contatti `/contatti` | Modulo CRM con referente, recapiti, città, preferenza oraria, motivo e messaggio; recapiti aziendali storici esposti | Campi essenziali e recapiti facoltativi progressivi; piano/MaxFull, CRM server e conferma | Anche interesse modulo selezionabile/modificabile/annullabile; payload con codice, consenso esplicito; nessuna conferma se manca risposta di registrazione valida |
| Accesso | `/login` e alias `/accesso`/HTML | Stessi accessi preservati | Nessuna modifica ad autenticazione, registrazione o area privata |
| Menu/footer | Link alle cinque pagine e accesso; dominio storico e recapiti | Cinque pagine, canonical sul dominio ufficiale, firma autore | Sei pagine con `/ampliamenti` autonoma, footer coerenti; vecchie ancore valide |
| Immagini | Illustrazioni e catture storiche la cui attualità non è attestata dal confronto del solo sorgente | Tre ambientazioni originali illustrative, cinque catture autentiche di fixture sintetiche | Stessi asset qualificati, senza introdurre persone/testimonianze o schermate inventate |
| Responsive | Markup/CSS storici consultati; nessuna nuova prova runtime eseguita sulla versione precedente | Qualifica isolata e online precedente distinta nel rapporto | Stile approvato mantenuto; nuova pagina e nuove interazioni attendono prova VM e ispezione screenshot |

## Informazioni recuperate e informazioni escluse con motivazione

Recuperati in forma più esplicita Tabella A, proprietà e assegnazioni multistruttura, modelli e storico comunicazioni Web Check-in, schedine individuali/famiglie/gruppi e percorsi di importazione/stampa/recupero. Calendario, notifiche interne, consegne e ticket erano già descritti e vengono conservati. Sono funzioni del software: non vengono automaticamente attribuite a un piano commerciale specifico.

Il catalogo vigente resta quello approvato dal proprietario il25ottobre: Solo50€ per1appartamento, Duo100€ per2, Multi200€ per3–7, Strutture320€ complessivi per1/2 strutture; oltre7appartamenti/oltre2strutture preventivo; IVA esclusa e abbonamento annuale. Max Full aggiuntivo a prezzo personalizzato. I riferimenti400€/60camere/prova7giorni appartengono ad altre fasi storiche e non vengono usati per rappresentare il listino del sito a90d63aa né quello attuale.

Il vecchio footer riportava Via Roma35 Bellaria Igea Marina, telefono/fax, info@tanggo.org, www.tangoschedine.com, Tanggo Experience ed ExperienceS.r.l.; mostrava «P.IVA: da inserire». La presenza nel vecchio HTML dimostra soltanto che il testo era pubblicato: non prova la validità odierna dei recapiti o la titolarità societaria. Non vengono rilanciati come confermati né viene inventata una partitaIVA. Form contatto e accesso riservato restano i canali verificabili. Restano da fornire dati societari/PartitaIVA, privacy, recapiti aggiuntivi e condizioni operative MaxFull.

## Segnalazione e limiti dell’indagine

L’utente ha riferito errore pagina e pulsanti inattivi. La rilettura statica della versione pubblicata ha trovato105ancore valide, sorgenti dell’anteprima identici e tour coerente. Le verifiche browser precedenti e la nuova riproduzione in sola lettura coordinata dal principale non hanno riprodotto il guasto segnalato; HTTP200 da solo non è considerato prova funzionale. Browser e messaggio specifico dell’errore non ancora disponibili.

Due dati concreti: la voce «Moduli e ampliamenti» non aveva una pagina autonoma, ora richiesta esplicitamente; gli URL CSS/JS non erano versionati e l’anteprima aveva una politica no-store diversa dalle risorse pubbliche. Il principale introduce URL con hash contenuto e marcatore unico di versione nel renderer per evitare riuso di risorse di versioni diverse. Questo è un intervento preventivo verificabile, non una causa della segnalazione già dimostrata. Nessuna affermazione che il browser dell’utente fosse in errore per cache.

## Verifiche da completare

Nuova qualifica del candidato nella VM ufficiale: sei pagine, link/ancore anche incrociati, alias, URL/versioni risorse, click reali, reload, indietro/avanti, tour completo e tastiera, selezione piano/servizio/interesse, validazione/CRM/errori e successo autentico. Dopo rilascio autorizzato, controlli online con sole fixture identificabili e rimozione delle sole righe proprie. I risultati vanno registrati dal principale nelle nuove evidenze, senza promuovere i PASS del sito precedente.
