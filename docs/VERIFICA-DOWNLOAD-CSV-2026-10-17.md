# Verifica del download CSV — 17 ottobre 2026

## Stato effettivo

**PARZIALE: download produttivo non completato.** Prima delle azioni, locale/GitHub main/VM quotidiana/produzione riletti a `7991511d6ce344bcbd24495fab89b2223dd81abb`. Produzione Git pulito, servizio FPM attivo, sorgenti/protetti conformi e nove sonde PASS. C5 e monitor precedenti conclusi conservati: nessuna nuova pubblicazione, manutenzione, DDL o ripetizione del monitor. Nessuna modifica applicativa o difetto applicativo accertato.

## Interfaccia produttiva

Nella sessione Superadmin/Hotel Amalfi, area **Liste, import ed export → Liste ed export**, inserito nel campo ricerca `VERIFICA-CSV-20261017-ZERO-7991511D`, quindi **Applica filtri**. Osservati quattro contatori a zero e link **CSV completo** con `q=VERIFICA-CSV-20261017-ZERO-7991511D&mode=general`. Clic sul solo link filtrato: Chrome mostra **Questa pagina è stata bloccata da Chrome**, `ERR_BLOCKED_BY_CLIENT`, documento `chrome-error://chromewebdata/`.

Nessun file scaricato tramite questa azione; nessun export non limitato, dato personale scaricato, indebolimento di protezioni/estensioni o cambio di canale. Non usati cURL, fetch, credenziali/sessioni alternative o accessi diretti per aggirare Chrome. La specifica causa del blocco client resta indeterminata; non attribuita arbitrariamente a un’estensione. Non osservata una risposta applicativa 4xx/5xx. **HTTP, Content-Type, Content-Disposition, nome e intestazioni del download online non osservabili**, quindi non dichiarati verificati dal solo codice o dai test.

Evidenze private: `output/verifica-csv-2026-10-17/browser.json`, `filtro-zero.jpg`, `esito-browser.jpg`. La pagina è stata riportata al filtro verificato a zero per il passaggio manuale; contesto struttura conservato.

## Contratto e prova isolata

`CustomerExportController::exportCsv` consente zero risultati e aggiunge sempre l’intestazione; non esiste un blocco applicativo dell’export vuoto. Atteso HTTP **200**, Content-Type **text/csv; charset=UTF-8**, Content-Disposition **attachment; filename="clienti_export_generale.csv"**. Contenuto: una sola riga, 17 colonne delimitate da virgole, tutte racchiuse fra virgolette:

```csv
"Codice","Nome","Cognome","Tipo cliente","Nazione","Citta","Gruppo I","Gruppo II","Gruppo III","Email","Telefono","Cellulare","Privacy","Marketing","Comunicazioni","Soggiorni","Ultimo arrivo"
```

Aggiunte quattro righe di asserzioni al test esistente `CustomerExchangeAreaTest::test_filtri_contatori_export_completo_consensi_e_ritorno`: zero risultati, status, header e contenuto integrale atteso. La stessa prova crea 18 clienti sintetici e un cliente estraneo: export completo di 19 righe anche con pagina 2, esclusione del tenant estraneo e filtri consensi; casi ruoli/strutture non assegnate nella suite. Test soltanto nel runtime ufficiale isolato della VM, mai su dati/server operativi.

Comando riproducibile: `python3 -B output/liste-import-export-2026-10-16/verifica.py`, che avvia il namespace ufficiale e `tests/Isolation/run.py`; selezione e comando completo conservati nello script e nel log privato `output/verifica-csv-2026-10-17/regressione.log`. Nuovo esito PHPUnit: **20 test / 337 asserzioni PASS**, identità/riconnessione/23 override rifiutati e build/GEO PASS. Tre prove browser a1440/768/390 PASS (48,3s); risorse effimere fermate/rimosse. Il test temporaneo nel checkout VM è stato ripristinato ai byte iniziali dopo confrontoSHA, senza modificare altro. I risultati isolati non attestano un download produttivo riuscito.

## Passaggio manuale necessario

1. Nella scheda produttiva ordinaria già aperta, mantenere Hotel Amalfi e aprire **Clienti → Liste, import ed export**, prima scheda **Liste ed export**.
2. Cercare esattamente `VERIFICA-CSV-20261017-ZERO-7991511D`, premere **Applica filtri** e controllare **Clienti filtrati 0** prima di procedere.
3. Premere personalmente **CSV completo**. Se riesce, verificare nei download il file `clienti_export_generale.csv`, aprirlo e confermare che contenga soltanto la riga delle 17 intestazioni sopra, senza clienti. Comunicare nome ed esito; non inviare file con dati reali.
4. Se compare ancora `ERR_BLOCKED_BY_CLIENT`, riferire quel risultato; non disabilitare protezioni o estensioni e non riprovare senza filtro. La verifica resta PARZIALE. Per attestare anche la risposta online, se disponibile nella normale scheda Rete degli strumenti sviluppatore, annotare status e i due header della sola richiesta filtrata, senza copiare cookie o altri header sensibili. Una risposta HTTP corretta da sola non completa il download.

## Pendenti e Git

Restano aperte, senza soluzione attribuita a questa verifica, la validazione completa dell’importatore non condivisa con il wizard e la normalizzazione autonoma via/civico assente. **BASELINE IN AUDIT invariata.** Solo test e documentazione selettivi da registrare; lavoro preesistente del Maestro e altri tre documenti esclusi. Gli SHA conclusivi e la distinzione fra commit documentale/test e applicazione distribuita sono nella ricevuta esterna, evitando autoreferenza. Nessuna necessità di distribuire il solo aggiornamento test/documentazione.
