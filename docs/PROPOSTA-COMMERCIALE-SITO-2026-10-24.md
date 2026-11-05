# Proposta commerciale del sito — aggiornamento 25 ottobre 2026

**PRONTO A REVISIONE; NON PUBBLICATO.** COMM2 sul candidato `a78d7bdb80c0c4c9d14bc8ae964b83b2a9eaad9e`: **33 test PHP / 303 asserzioni e 6 percorsi browser superati**, con build/GEO, 23 rifiuti di override e 4.391 digest sorgente verificati. Ispezionate tutte le 23 immagini finali. Questa qualifica sostituisce i risultati preliminari della nuova proposta; le precedenti fasi R4/R6/R7 restano storiche e non vengono sommate. BASELINE IN AUDIT invariata. Nessuna pubblicazione, modifica fiscale o dato reale. Il presente aggiornamento è solo documentale: codice e risorse qualificati restano congelati.

## Ambito e proprietà

Worktree e ramo web esistenti preservati. Agente web: cinque HTML pubblici, CSS/JS canonici e relativi due specchi, spec browser dedicato e questo rapporto. Agente principale: route, test PHP/CRM, sitemap/robots, Maestro e ambiente ufficiale delle acquisizioni. Unica modifica backend: limite di sei richieste al minuto sul POST contatti esistente (firma utente per autenticati, IP per visitatori anonimi), con prefisso `sito-contatti` separato dal limiter Tassa. Controller CRM, schema, autenticazione e logica di attivazione invariati. Creato `public/sitemap.xml` e aggiornato `public/robots.txt`, riferiti al dominio ufficiale confermato `https://schedinedinotifica.tanggo.software`. Nessuna nuova route necessaria: Funzionalità e Moduli e ampliamenti condividono la pagina esistente e ancore stabili.

## Implementazione corrente

- CTA «Richiedi la prova di 7 giorni» in navigazione e percorsi commerciali, destinazione `/contatti#prova`.
- Motivo esatto «Prova di 7 giorni» nel modulo esistente e preselezione per hash, anche nel passaggio all’interno della stessa pagina. Richiesta di attivazione manuale, senza promessa di accesso immediato o creazione di account.
- Home con beneficio per la reception, flusso, FAQ e accesso alla demo; marchio Tanggo Platform preservato.
- Nove sezioni dei moduli con problema quotidiano, esempio, requisiti e invito al contatto; nessuna roadmap o funzionalità futura inventata.
- Recapiti opzionali raccolti in espansione facoltativa: dati principali e payload CRM esistente preservati.
- Demo interattiva di cinque passaggi, tastiera con frecce/Home/End, pannello identificato, comandi avanti/indietro e indicazione del passaggio. I contenuti rimangono leggibili senza JavaScript.
- Cinque immagini autentiche acquisite nel runtime sintetico ufficiale e integrate nella hero e nei cinque pannelli della demo. ACQ1: un percorso browser superato in 7,1 secondi, con build/GEO e 23 rifiuti di override verificati dal principale; questa prova riguarda l’acquisizione, non qualifica il nuovo sito completo. WebP lossless corrispondenti ai pixel delle aree originali; esclusa la barra account, nessuna schermata inventata o dato personale reale utilizzato. Dimensioni esplicite, hero prioritaria e altre catture a caricamento differito. Link all’immagine originale sempre disponibile. Nessun video trovato nel repository: percorso interattivo previsto dall’incarico, senza player vuoto.

## Fonti e limiti commerciali

Prevalgono Maestro e modelli/controllori reali già riscontrati nella matrice del rapporto precedente. Arrivi segue la giornata Europe/Rome, non un calendario prenotazioni. Web Check-in conserva richiesta, referente, compilazione parziale e revisione distinta. Alloggiati Web richiede configurazione e credenziali pertinenti. ISTAT/Ross1000 è contestualizzato all’Emilia-Romagna; accettazione esterna e attivazione non vengono certificate. Tassa dipende da Comune, categoria e periodo supportati: preparare file non prova accettazione, incasso o versamento.

Nomi ufficiali verificati dal principale sulle fonti primarie: [Alloggiati Web](https://alloggiatiweb.poliziadistato.it/portalealloggiati/), [rilevazioni turismo Emilia-Romagna](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo), [manuali e tracciati](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo). Nessuna attestazione di compatibilità o invio automatico universale.

## Verifiche concluse e precedenti conservati

Esecuzioni svolte dall’agente principale esclusivamente nella VM ufficiale e nel runtime effimero isolato; nessun test Laravel sul sistema host.

| Esecuzione | Perimetro ed esito |
| --- | --- |
| ACQ1 | Acquisizione autentica del prodotto: 1 percorso browser superato in 7,1 secondi, con build/GEO e 23 rifiuti di override. Separata dalla qualifica commerciale. |
| COMM1 | 33 test PHP / 303 asserzioni superati; browser 5 superati e 1 non superato a 768 px. Dopo `scrollIntoViewIfNeeded`, la visibilità integrale dell’immagine risultava 0,9992792606 anziché 1. Originali conservati. |
| COMM2 | 33 test PHP / 303 asserzioni e tutti i 6 percorsi browser superati sul candidato sopra indicato; riepilogo browser 1,2 minuti. 23 PNG finali, build/GEO, 23 rifiuti di override e 4.391 digest sorgente verificati. |

COMM2 centra l’immagine con scorrimento istantaneo e attende due frame prima della cattura, mantenendo invariata l’asserzione di visibilità completa `ratio: 1`. La ripetizione è superata; la causa tecnica esatta del valore frazionario precedente non è dimostrata. Nessun aumento dei timeout, occultamento di contenuti o riduzione delle asserzioni. Corretto anche l’ambito delle soglie CSS: navigazione a 1.280 px, griglie a 950 px; a 1.266 px la hero mantiene due colonne.

I controlli coprono:

- cinque pagine a 1.440, 768 e 390 px, per 15 combinazioni; navigazione e CTA anche a 1.280, 1.266, 1.151 e 1.024 px, con contenimento del testo nel pulsante e nella viewport;
- motivo «Prova di 7 giorni» conservato nel lead e nell’attività CRM; nessuna creazione o attivazione di account/strutture; cambio volontario del motivo rispettato;
- campi richiesti, honeypot, limite antispam e separazione fra IP di visitatori anonimi; messaggio 429 italiano, dati conservati e possibilità di ripetere l’invio;
- autenticazione e accessi privati preservati, navigazione da tastiera, focus della conferma e ritorno al pulsante;
- hash HTTP di CSS, JS, cinque WebP, sitemap e robots; risorse caricate, dimensioni naturali delle immagini, zero errori JS/asset e zero overflow nelle combinazioni controllate;
- tutti i cinque pannelli della demo e tastiera con frecce/Home/End, comandi ai limiti del percorso, ancore e FAQ.

Tutte le **23 immagini COMM2** sono state aperte e ispezionate: 15 pagine complete, tre conferme, tre viste della demo, una hero desktop e una sezione Novità. Gerarchie, griglie, form, CTA e schermate del prodotto risultano coerenti, senza tagli laterali o sovrapposizioni che impediscano la lettura. Le viste della demo sono catture della pagina già scorsa: la barra fissa resta in alto e parte dell’introduzione può trovarsi sopra la viewport; l’immagine del prodotto è interamente visibile. Su telefono la schermata mantiene il rapporto originale e il collegamento permette di aprirla a dimensione piena.

Analisi statica: `git diff --check`, sintassi Node di JS/spec, unicità di H1/ID/campi, ancore e parità degli specchi superati. Esiti delle esecuzioni ripetute non sommati.

Runtime e fixture effimere conclusi e rimossi; daily `945ef553` e SELinux Enforcing preservati. Osservatore di capacità: 23 campioni (uno precedente al backup), minimo VM 2.050.174.976 byte, superiore alla riserva di 1 GiB, e minimo Mac 28.910.243.840 byte; dopo COMM2, prima del passaggio, spazio VM 6.241.198.080 byte. VM restituita espressamente al fronte ISTAT: nessun ulteriore utilizzo in questa consegna. Nessuna email, trasmissione a enti o modifica di dati condivisi/reali.

## Anteprima e limiti residui

Anteprima locale navigabile: `http://127.0.0.1:63351/`, solo loopback e con banner di revisione. La conferma del form nell’anteprima è dichiaratamente dimostrativa e non scrive nel database né invia email; il salvataggio CRM reale è stato verificato separatamente con dati sintetici nel runtime effimero.

Nessun video autentico utilizzabile trovato: consegnato il percorso interattivo completo con catture autentiche, come alternativa espressamente richiesta, senza player o pulsanti video fittizi. Dati societari, privacy, recapiti e listino/condizioni commerciali restano da confermare prima della pubblicazione. Le verifiche esterne dei moduli e l’abilitazione dei relativi canali restano distinte dalla qualifica del sito. **Nessuna pubblicazione in produzione eseguita.**

## Provenienza delle schermate

Acquisizione del 25 ottobre 2026, nel runtime ufficiale effimero, senza dati reali o invii. Le schermate mostrano Hotel Dimostrativo e ospiti fittizi. Il riepilogo Tassa è un esempio Bellaria, giugno 2026: la tariffa visibile di 1,50 euro appartiene al caso fiscale dimostrativo, non al prezzo del software. Le barre account sono escluse dai ritagli; nessun pixel del prodotto ricostruito o alterato. Originali e manifest privato conservati dall’agente principale; nessun log grezzo incluso nel sito.

Le cinque immagini WebP pesano complessivamente 172.172 byte (circa 168 KiB), con compressione lossless e senza librerie o richieste di terze parti aggiuntive.

## Evidenze consegnate

[Ricevuta aggregata](evidenze/proposta-commerciale-sito-2026-10-24/verifica.json), con SHA qualificato e digest delle immagini. Selezione originale dalle 23 immagini COMM2: [hero desktop](evidenze/proposta-commerciale-sito-2026-10-24/sito-home-hero-desktop.png), [home mobile](evidenze/proposta-commerciale-sito-2026-10-24/sito-home-390.png), [percorso demo tablet](evidenze/proposta-commerciale-sito-2026-10-24/sito-demo-percorso-768.png), [conferma mobile](evidenze/proposta-commerciale-sito-2026-10-24/sito-conferma-390.png). Evidenze precedenti e originali NONPASS preservati separatamente; nessun log grezzo o dato privato incluso nella consegna.
