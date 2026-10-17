# Logo Rimini e pastello Clienti — 17 ottobre 2026

Scope: correggere il caricamento in GEO → Comuni e ammorbidire soltanto la selezione Nuovo / Lista. Nessuna modifica a validazioni, controller, permessi, storage, schema o altri moduli.

Il browser isolato riproduce la perdita del file durante le conferme: la risposta tardiva della ricerca sostituisce la riga. Il nodo originale conserva il file ma risulta staccato dal documento; quello nuovo è vuoto e nessun POST parte. Il difetto è coerente con il comportamento PNG/WebP riferito dall’utente, senza attestare che sia l’unica causa possibile dei suoi tentativi. In produzione Rimini era senza logo; storage e limiti PHP controllati in sola lettura, nessun errore pertinente nei log osservati.

La sola vista GEO invalida le risposte di ricerca pendenti quando si entra nel campo file o se ne cambia la selezione e conserva la ricerca nel ritorno. La selezione Clienti usa una classe dedicata e lo stesso gradiente della sidebar, senza modificare le altre schede.

Prove definitive nel runtime ufficiale isolato: 21 test PHP / 178 asserzioni; 12 prove browser; 15 guardie e 23 rifiuti di override; build e GEO immutabile PASS. Otto upload reali nel browser per PNG, WebP, JPG/JPEG a 1440/390 px, con risposta POST 302 e immagine visibile. Errore di validazione visibile e logo precedente conservato; errore storage simulato nel test PHP con messaggio e conservazione. Tre prove del pastello a 1440/768/390 px confrontano il colore con la sidebar e verificano i controlli. Nessun logo reale caricato o sostituito durante queste prove.

Tentativi conservati: prima fixture con duplicazione del paese sintetico, corretta nel solo test; riproduzioni negative con file perso; prima correzione con upload riuscito ma ritorno senza ricerca, completata nella vista; esecuzione definitiva verde. Nessun aumento di timeout o retry. Primo preflight durante i test temporanei sotto soglia; dopo la loro rimozione automatica, 3.815.899.136 byte liberi contro 3.709.771.776 richiesti: PASS a soglie e margine di 1 GiB invariati. Nessuna pulizia condivisa o crescita VM.

Pubblicazione C5 autorizzata dall’utente, ancora da eseguire con nuovo PREPARE/S0/guardiano, controlli di integrità e monitoraggio di almeno 900 secondi. [Evidenze](evidenze/logo-rimini-pastello-2026-10-17/verifica.json). Modifiche estranee preservate. BASELINE IN AUDIT.
