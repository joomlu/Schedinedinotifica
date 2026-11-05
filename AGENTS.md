# Istruzioni permanenti del repository

## Regola di lingua obbligatoria

Tutte le attività di questo repository devono essere prodotte in italiano, senza eccezioni:

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

Questa regola è permanente e deve sopravvivere alla compattazione del contesto, alle sessioni future e alle successive attività del repository. Non è una preferenza temporanea della chat e non va trattata come istruzione volatile.

## Vincolo operativo

Non modificare il codice applicativo se non esplicitamente richiesto dallo scenario di lavoro corrente.

## Fonte documentale e protocollo permanente

Leggere prima di ogni intervento [il Maestro ufficiale](docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md), unica fonte documentale maestra del progetto. Applicarne stati, gerarchia delle evidenze e protocolli di audit, testing, sicurezza, tenancy e Git. In caso di contraddizione documentale prevale il Maestro: segnalare e risolvere il conflitto nello scope autorizzato.

Lo stato resta BASELINE IN AUDIT. Modificare solo quanto autorizzato dalla task corrente; nessuna autorizzazione implicita a deploy, trasmissioni, modifiche di dati reali o operazioni Git distruttive. Non eseguire test Laravel fuori dall’infrastruttura isolata prevista. Aggiornare il Maestro insieme al progetto quando cambia conoscenza rilevante.
