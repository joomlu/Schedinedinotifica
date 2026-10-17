# Riordino visivo Nuovo / Lista Clienti — 17 ottobre 2026

Unico accesso laterale e due schede grandi sopra le viste esistenti. Collegamenti alle route originarie, scheda attiva con aria-current, ritorno Lista con filtri/ordine/pagina per struttura. Modulo e lista riutilizzati senza modifiche interne; nessuna modifica di logica, query, permessi o database.

Verifiche isolate:36testPHP/356asserzioni,11browser esistenti e3browser di navigazione1440/768/390PASS; build/GEO e23override rifiutatiPASS. Creazione, lista, consultazione, modifica e stampa coperti; nuove prove tastiera, contesto, dimensioni pulsabili e recupero bozza senza richieste di scrittura. Primo tentativo navigazione1PASS/2FAIL per compilazione prima dell'inizializzazione, corretto nel solo test. Digest e preflight in [verifica.json](evidenze/navigazione-clienti-2026-10-17/verifica.json).

Pubblicazione richiesta successivamente dall'utente. Preflight corrente:1509302272byte liberi contro3709771776richiesti, margine1GiB invariato. STOP prima PREPARE/manutenzione/S0/RUN: nessuna pubblicazione o verifica online del riordino attestata. Necessari recupero spazio autorizzato o ampliamento con salvaguardie, nuovo preflight e nuova C5 completa. Il precedente rilascio692cb292 resta distinto da questa modifica.

Modifiche estranee preservate ed escluse dal commit. BASELINE IN AUDIT invariata.

Ripresa autorizzata: commit applicativo `adb874c5` presente su GitHub main (push confermato). Pulizia di24.128soli derivati vendor/build in recuperi chiusi, SHA/HMAC delle copie cifrate verificati, nessun processo/mount; DB/SQL/env/chiavi/upload/backup/diagnostici e lavori in corso esclusi. Recupero netto517.505.024byte; preflight successivo1.999.900.672liberi contro3.709.771.776richiesti: mancano1.709.871.104byte. **STOP prima PREPARE/manutenzione**, nessuna ulteriore eliminazione o ampliamento; proposta VM28→32GiB con backup/verifiche, da autorizzare. Primo inventario fermato da deposito cifrato incompleto, nessuna cancellazione; seconda verifica solo con custodie complete/autenticatePASS. [Evidenza](evidenze/navigazione-clienti-2026-10-17/pulizia-e-preflight.json). BASELINE IN AUDIT invariata.
