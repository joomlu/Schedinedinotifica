# Istruzioni persistenti per GitHub Copilot

## Lingua obbligatoria

Tutte le attività di questo repository devono essere prodotte in italiano, senza eccezioni.

- messaggi intermedi;
- spiegazioni;
- diagnosi;
- report;
- titoli;
- conclusioni;
- risultati dei test;
- descrizione delle modifiche;
- rischi;
- verdetti;
- domande;
- proposte per il passo successivo.

Non si passa all'inglese durante il lavoro, anche se:

- il codice è in inglese;
- i comandi del terminale sono in inglese;
- PHPUnit o Laravel producono output in inglese;
- la documentazione tecnica è in inglese;
- il contesto viene compattato;
- viene aperta una nuova sessione;
- viene eseguita una lunga sequenza di tool.

Sono ammessi in inglese solo:

- codice sorgente quando richiesto dal progetto;
- nomi tecnici esistenti;
- nomi di classi, metodi, route e variabili;
- comandi shell;
- output letterale degli strumenti quando deve essere riportato fedelmente;
- messaggi di errore originali, se utili alla diagnosi.

Ogni spiegazione di tali elementi deve comunque essere in italiano.

## Persistenza della regola

Questa regola è permanente e deve sopravvivere alla compattazione del contesto, alle sessioni future e alle successive attività del repository. Deve essere rispettata in tutte le fasi successive del progetto.

## Vincolo operativo

Non modificare il codice applicativo se non esplicitamente richiesto. Questa regola non autorizza modifiche al codice applicativo in questa fase.

## Fonte documentale ufficiale

Leggere [il Maestro ufficiale](../docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md) prima di ogni intervento e rispettarne stati, evidenze e protocolli. È l’unica fonte documentale maestra; in caso di contraddizione documentale prevale il Maestro, entro lo scope autorizzato dalla task.

Lo stato resta BASELINE IN AUDIT, senza certificazione di produzione. Nessun test Laravel fuori dall’infrastruttura isolata. Nessun deploy, trasmissione o modifica di dati reali implicitamente autorizzati. Aggiornare il Maestro quando cambia conoscenza rilevante del progetto.
