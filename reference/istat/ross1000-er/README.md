# Specifiche pubbliche utilizzate dal modulo ISTAT

Verifica del 5 ottobre 2026. Nessun dato di clienti, credenziale o export reale.

- Regione Emilia-Romagna, [pagina vigente dei manuali Ross1000](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo): indica XML-WS 2.4, pagina aggiornata il 12 giugno 2025.
- [Tracciato regionale XML-WS 2.4, 29 settembre 2021](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/tracciatoxml-webservice.pdf/@@download/file): ancora pubblicato dalla pagina vigente. Definisce il file di movimentazione e i domini descrittivi. Non si presume valido soltanto perché disponibile in un vecchio archivio.
- [WSDL regionale pubblico](https://datiturismo.regione.emilia-romagna.it/ws/checkinV2?wsdl): scaricato senza autenticazione, contiene due schemi e il contratto SOAP. SHA256 originale `5ee868bda0ffcd34c5eff41bbc9bf0c398903d93a0407f00ec6c1804d47bc309`.
- Polizia di Stato, [tabelle ufficiali](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Tabelle.aspx): la specifica regionale richiede queste codifiche. Snapshot Comuni, Stati e Tipo Alloggiato scaricati pubblicamente, senza accesso autenticato. Non sono modifiche al modulo Questura o ai dati GEO.

`wsdl.xml` è lo snapshot integrale. `checkin.xsd` e `v2.xsd` sono gli schemi estratti dal WSDL: si aggiunge esclusivamente `schemaLocation` agli import reciproci per risolverli localmente, senza cambiare tipi, cardinalità o sequenze.

`file-binding.xsd` è dichiaratamente un **binding locale**, non un XSD autonomo pubblicato dalla Regione: associa la radice senza namespace del file descritto nel PDF al tipo `V2:xmlImportDati` ufficiale. La validazione SOAP utilizza direttamente `checkin.xsd`. Il binding permette il controllo del file senza aggiungere namespace non previsti dal PDF.

I tipi XSD sono permissivi su date, codici e obbligatorietà di alcuni elementi. Il loro superamento è necessario ma insufficiente: il modulo verifica separatamente dati nominativi, domini, provenienze, composizione del soggiorno e calendario. La FAQ regionale considera consigliati alcuni campi statistici che il PDF presenta come obbligatori: il modulo applica prudenzialmente il requisito più restrittivo a turismo/trasporto, segnala il dato mancante e non inventa un valore.

Per aggiornare gli snapshot occorre confrontare la pagina regionale, il WSDL e le tabelle pubbliche; poi ripetere i test isolati. Gli snapshot non attestano automaticamente future revisioni delle codifiche.

Le tre tabelle sono conservate con terminatori LF per rispettare i controlli Git del progetto; encoding e tutte le celle sono invariati rispetto ai download CRLF. `provenienza.json` registra URL, SHA256 originali e normalizzati e quantità di record; confronto delle celle superato integralmente.
