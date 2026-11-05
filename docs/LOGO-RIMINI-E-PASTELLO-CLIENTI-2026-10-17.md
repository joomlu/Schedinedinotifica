# Logo Rimini e pastello Clienti — 17 ottobre 2026

Scope: correggere il caricamento in GEO → Comuni e ammorbidire soltanto la selezione Nuovo / Lista. Nessuna modifica a validazioni, controller, permessi, storage, schema o altri moduli.

Il browser isolato riproduce la perdita del file durante le conferme: la risposta tardiva della ricerca sostituisce la riga. Il nodo originale conserva il file ma risulta staccato dal documento; quello nuovo è vuoto e nessun POST parte. Il difetto è coerente con il comportamento PNG/WebP riferito dall’utente, senza attestare che sia l’unica causa possibile dei suoi tentativi. In produzione Rimini era senza logo; storage e limiti PHP controllati in sola lettura, nessun errore pertinente nei log osservati.

La sola vista GEO invalida le risposte di ricerca pendenti quando si entra nel campo file o se ne cambia la selezione e conserva la ricerca nel ritorno. La selezione Clienti usa una classe dedicata e lo stesso gradiente della sidebar, senza modificare le altre schede.

Prove definitive nel runtime ufficiale isolato: 21 test PHP / 178 asserzioni; 12 prove browser; 15 guardie e 23 rifiuti di override; build e GEO immutabile PASS. Otto upload reali nel browser per PNG, WebP, JPG/JPEG a 1440/390 px, con risposta POST 302 e immagine visibile. Errore di validazione visibile e logo precedente conservato; errore storage simulato nel test PHP con messaggio e conservazione. Tre prove del pastello a 1440/768/390 px confrontano il colore con la sidebar e verificano i controlli. Nessun logo reale caricato o sostituito durante queste prove.

Tentativi conservati: prima fixture con duplicazione del paese sintetico, corretta nel solo test; riproduzioni negative con file perso; prima correzione con upload riuscito ma ritorno senza ricerca, completata nella vista; esecuzione definitiva verde. Nessun aumento di timeout o retry. Primo preflight durante i test temporanei sotto soglia; dopo la loro rimozione automatica, 3.815.899.136 byte liberi contro 3.709.771.776 richiesti: PASS a soglie e margine di 1 GiB invariati. Nessuna pulizia condivisa o crescita VM.

Pubblicazione C5 autorizzata dall’utente, ancora da eseguire con nuovo PREPARE/S0/guardiano, controlli di integrità e monitoraggio di almeno 900 secondi. [Evidenze](evidenze/logo-rimini-pastello-2026-10-17/verifica.json). Modifiche estranee preservate. BASELINE IN AUDIT.

## Pubblicazione effettiva

Pubblicato il candidato **ed6121f546e1588398823e278a574d6127af0a44** con i soli interventi autorizzati. Il focus o la selezione del file invalida le risposte di ricerca ancora pendenti, preservando modulo e ricerca di ritorno; Nuovo / Lista usa il gradiente pastello della sidebar. Controller, validazioni, permessi, storage e database invariati. [Consuntivo](LOGO-RIMINI-E-PASTELLO-CLIENTI-2026-10-17.md) ed [evidenze](evidenze/logo-rimini-pastello-2026-10-17/rilascio.json).

Nuovo PREPARE indipendente PASS; S0 fresco recuperato e verificato in 143.62 secondi; RUN concluso in 223.207 secondi, CANDIDATO_ACCETTATO e zero DDL. Guardiano disarmato, inattivo e disabilitato. Database produttivo identico byte per byte al nuovo S0 anche dopo il browser; 74 persistenti, .env, APP_KEY, upload e loghi originali conservati. Nessuna ulteriore pulizia o crescita VM; capienza finale 1435287552 byte.

21 test PHP / 178 asserzioni, 12 browser, 15 guardie, 23 rifiuti di override e build/GEO PASS. Upload completi PNG/WebP/JPG/JPEG a 1440/390 px con POST e HTTP 302 osservati, immagine caricata; errore di validazione visibile e logo precedente conservato. Errore di scrittura simulato nel test PHP con messaggio e conservazione. Online: Rimini RN 099014, form multipart e formati corretti; ricerca e ingresso nel campo file; nessuna scrittura di prova sul Comune reale. Rimini resta senza logo, non essendo stato fornito un file reale. Pastello di Nuovo a 1440/768/390 px e Lista verificato con confronto dei colori effettivi con la sidebar; nessun errore browser osservato.

Monitor reale di **918.472 secondi, 31 campioni e 279 sonde HTTP/TLS/CSS PASS**. Zero nuovi errori applicativi o FPM dal T0; Git produttivo pulito, FPM attivo e barriere assenti. SHA applicativo locale/GitHub/VM/produzione allineato al candidato prima del consuntivo; commit conclusivo soltanto documentale, SHA finali nella ricevuta esterna `output/logo-rimini-pastello-2026-10-17/ricevuta-finale.json`. Prove, riproduzioni negative e limiti conservati. Modifiche estranee preservate ed escluse. **BASELINE IN AUDIT invariata**.

Il flusso locale del monitor è rimasto parziale al quattordicesimo campione; la ricevuta conclusiva è stata verificata direttamente sul server: 918,472 secondi, 31 campioni e 279 sonde PASS. Nessuna causa del mancato aggiornamento locale è attribuita senza prova. La verifica server conclusiva, successiva al monitor, riconferma DB/S0, persistenti, SHA, FPM e sonde.
