# ISTAT — piano concreto di capienza per C5

## Consuntivo del 25 ottobre 2026

Piano specificamente autorizzato ed eseguito: backup integrale cifrato dei 12 file verificato, disco da 40 GiB e XFS da 41.585.250.304 byte; unica modifica Lima al campo disco. Database logico e 84 file protetti identici prima/dopo; daily `945ef553` e SELinux Enforcing preservati. Nuovi PREPARE/S0, recuperi indipendenti e custodie Extra PASS; ISTAT pubblicato e verificato con C5. Nessun backup precedente eliminato, soglie e budget invariati.

[Ricevuta risorse](evidenze/istat-integrale-2026-10-24/crescita-VM.json), [consuntivo rilascio](evidenze/istat-integrale-2026-10-24/rilascio.json). Il piano seguente conserva lo stato e la proposta anteriori al consenso.

## Piano originario, stato storico superato

Proposta non eseguita. VM `schedine-sviluppo-interno`, disco fisico interno `/Users/jorgeluccitelli/.lima/schedine-sviluppo-interno/disk`, 36 GiB, filesystem `/dev/vda3` XFS, nessun mount host. Ultima misura a runtime concluso: 2.072.559.616 byte liberi. Il precedente C5 richiedeva dopo PREPARE almeno 3.097.755.648 byte residui (comprensivi della riserva 1 GiB); la nuova catena completa richiede anche la base PREPARE. Nessuna finestra di produzione è stata aperta.

Delta proposto: portare esclusivamente il disco di questa VM da 36 a 40 GiB (+4.294.967.296 byte). Libero teorico dopo crescita circa 6,37 GB, prima del nuovo PREPARE. La proiezione non sostituisce misure effettive e preflight aggiornato. Mac sopra 8 GiB, Extra sopra 40 GiB e guest con riserva di 1 GiB restano requisiti invariati.

Sequenza dopo autorizzazione specifica:

1. Coordinare con agente web una finestra senza collaudi; attestare identità della VM, daily `945ef553`, SELinux Enforcing, spazio e assenza di processi propri attivi.
2. Arrestare ordinatamente soltanto questa VM; salvare configurazione e impronta integrale del disco fermo. Creare una nuova copia cifrata completa su Extra, con chiave nella custodia interna separata; verificare autenticazione, decifratura completa e identità dei byte prima di mutare il disco. Nessuna eliminazione di backup o recuperi precedenti.
3. Ampliare soltanto questo disco a 40 GiB attraverso Lima e verificare la configurazione. Avviare e verificare partizione/XFS; espandere esclusivamente il filesystem previsto se necessario con procedura già verificata. Non formattare e non importare database.
4. Riattestare dati quotidiani, file protetti, identità, SELinux, capacità e margini; in caso di difformità fermarsi, conservare entrambe le copie e utilizzare il backup verificato per recuperare la VM originaria.
5. Generare nuovo kit/candidato C5, nuovi PREPARE/S0 e recuperi indipendenti, misurando continuamente il consumo. Nessun cambio a timeout, riserva o guardrail. Pubblicare soltanto dopo tutti i gate previsti; nessuna dichiarazione ISTAT reale.

Non sono autorizzati da questo piano: pulizie di backup/recuperi, cambi DNS o vhost, modifiche dati reali, nuove configurazioni dei servizi esterni, trasmissioni statistiche o pubblicazione del sito ancora in revisione.
