# Access multiproprietario → Bozze Schedina

26 novembre 2026. Circuito pubblicato e verificato nel perimetro; BASELINE IN AUDIT generale invariata.

- [Reception Access](https://access.tanggo.software/) · [Portale ospite](https://checkin.tanggo.software/) · [Schedina](https://schedinedinotifica.tanggo.software/). Il portale ospite richiede il link personale generato dall'invito.
- Proprietario demo Tanggo: Igea Marina94 e Bellaria95. Rimini non collegata.
- Tre schede reali assegnate esplicitamente a Igea, mantenendo immagini, revisioni e scadenze originali. Nessuna trasmissione automatica delle schede reali.
- Lettore del proprietario: postazione browser, condivisibile tra i suoi hotel; struttura scelta prima della lettura. È possibile fissare un lettore a un solo hotel.
- Documento salvato → soggiorno e componenti controllati → **Invia a Schedina** → ricevuta della bozza → apertura e completamento nel gestionale. Nessuna conferma implicita dell'identità o dell'ingresso dal solo documento.
- Circuito ospite: invito → documenti e completamento dati → approvazione operatore → selfie → confronto visivo → check-in → bozza, riusando la pratica documentale già inviata.

Ricevente337ca5aa (PR67), Access9b85db8b (PR1–4), release `20261126-multiutente-v3`. Account e password dei due programmi restano indipendenti. Le credenziali personali non sono conservate nel repository.

Qualifica:98test Node;20test PHP/103asserzioni nella VM ufficiale; build/GEO e23guardie positivi. Recuperi qualificati prima delle finestre e checkpoint SQLite verificati. Una sonda con User-Agent rifiutato è stata corretta senza toccare i filtri; un blocco HTTP dopo rifiuto è stato riprodotto e risolto. Vincoli di identificativo e assegnazione verificati senza accessi incrociati.

Prove online:541/Igea,542/Bellaria (3persone,2componenti,1camera,3letti);543/Igea scaduta con avviso persistente. Doppio invio e successivo check-in senza duplicati; chat bidirezionale, selfie fittizia, approvazione e PIN dimostrativo. Chrome ha richiesto il permesso camera e aperto il video640×480 senza scattare o inviare immagini personali. Le fixture sono chiuse e nel cestino recuperabile, il lettore di prova è disabilitato e le bozze restano disponibili.

Limiti: completare in Schedina i riferimenti geografici, i titoli e le classificazioni realmente mancanti; nessun consenso presunto o invio a Questura/ISTAT. Non sono integrate serrature o SDK hardware. L'OCR richiede controllo operatore: l'esempio incorporato non ha restituito campi nel controllo locale e la prova aggiuntiva di upload automatico si è interrotta per la sessione browser; non è attestato il riconoscimento universale su documenti reali.

[Manuale operativo e prove del componente indipendente](https://github.com/joomlu/tanggo-access/blob/main/MANUALE-MULTIPROPRIETARIO.md).
