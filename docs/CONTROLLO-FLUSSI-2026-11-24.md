# Controllo dei flussi — 24 novembre 2026

Richiesta: verificare la presentabilità dell'intero programma, correggere ordine delle colonne Tassa/Tariffa e stile del filtro Compilazione Web Check-in, fornire rapporti prima del successivo percorso guidato di check-in.

## Perimetro ed evidenze iniziali

Due soli template applicativi modificati, nessun servizio o controller, migrazione, algoritmo fiscale, trasporto o privilegio cambiato. Verifica browser sintetica positiva a 1440/768/390 su colonne e valori effettivi, selezione/ricerca/cancellazione del filtro, conservazione della ricerca, quattro stati del web check-in e cinque schede Presenze. Zero scritture da navigazione, overflow ed errori JavaScript nelle tre prove.

Ricognizione online circoscritta ai tre hotel demo: 60 pagine funzionali HTTP 200 e tre accessi Superadmin rifiutati come previsto, zero anomalie osservate. Tutte le richieste di modifica/invio, eccetto il normale login, sono state bloccate nel browser dell'audit. Inventario in sola lettura: 12 schedine, zero richieste web, zero trasmissioni Questura/ISTAT e zero consolidamenti Tassa. Nessun dato reale modificato e nessun invio eseguito.

## Suite generale

R1: raccolti **1.336 casi** dalla suite ordinaria di 116 file `*Test.php`; runtime ufficiale con rete isolata, database sintetico attestato, build/GEO e 23 rifiuti di override positivi. Il processo PHPUnit ha esaurito il proprio limite di 256 MB dopo l'avvio di circa un terzo della suite, con alcuni fallimenti/errori precedenti al limite. La prova non è un successo e non permette un verdetto globale.

R2: ripetizione in corso con 1024 MB nel solo `phpunit.xml` della copia privata della VM. Il file del progetto e la configurazione di produzione restano a valore invariato; asserzioni, guardie e isolamento non sono stati allentati. Prevista conservazione del JUnit e dell'output integrali dei soli dati sintetici, prima della normale pulizia del runtime. Risultati e classificazione delle eventuali anomalie saranno aggiunti al completamento.

## Giudizio corrente

Le due correzioni sono qualificate nel browser; la panoramica dei dati è osservabile con i limiti descritti nel [rapporto di presentazione](PANORAMICA-PRESENTAZIONE-2026-11-24.md). Non dichiarare verificato positivamente ogni flusso del sistema finché la suite generale non è completata e gli eventuali fallimenti non sono analizzati. **BASELINE IN AUDIT invariata.**
