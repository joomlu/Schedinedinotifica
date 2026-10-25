# Sito commerciale — completamento integrale del 25 ottobre 2026

**CANDIDATO IN QUALIFICA; nuova pubblicazione non ancora eseguita. BASELINE IN AUDIT invariata.**

L’utente ha richiesto esplicitamente confronto con il sito sostituito, audit del prodotto, implementazione, verifiche e pubblicazione. Scope: sito pubblico Laravel e contatti CRM, senza cambiare logica del gestionale, schema o dati reali. Rischio medio per contatto pubblico, gestione delle risorse e rilascio. Lavoro nel worktree indipendente `sito-integrale-2026-10-25`, base main1a2e5e1f; nessun reset o sovrascrittura dei worktree precedenti.

## Confronto e contenuti

[Matrici e fonti](CONFRONTO-SITO-2026-10-25.md), [funzioni e requisiti](AUDIT-CONTENUTI-SITO-2026-10-25.md). Identificata la versione sostituita a90d63aa dal rilascio effettivo precedente: il suo listino Basic200/ProfessionalPro340/Enterprise su richiesta non viene confuso con altre proposte storiche. Listino finale approvato50/100/200 appartamenti e320complessivi per1/2strutture, annualeIVAesclusa, preventivi oltre soglie eMaxFull personalizzato invariati.

Recuperati o resi espliciti TabellaA, proprietari e multistruttura, modelli e storico WebCheck-in, famiglie/gruppi, importazioni/stampe/recupero. Nuova `/ampliamenti` autonoma, menu/footer/CTA coerenti, vecchia `/moduli#stato-funzionalita` conservata. Nessuna integrazione o accettazione esterna inventata.

## Problemi riscontrati e correzioni

- La vecchia voce ampliamenti indicava una sezione di `/moduli`, non una pagina propria. L’accesso diretto a `/ampliamenti` cadeva sul catch-all autenticato:302versologin. Aggiunta rotta pubblica nominata, pagina dedicata e aliasHTML.
- La conferma contatto si basava sul solo HTTP200. Riprodotti sulla baseline tre falsi successi con JSONsenza ricevuta/okfalse/rispostaHTML. Ora successo soltanto con oktrue e lead_id intero positivo, dati conservati su errore.
- CSS/JS avevano URL invarianti; introdotti nomi con hash del contenuto, verifica sul server e firma comune header/meta delle sei pagine. AssetGET senza stato/cookie, pagine private e protezioni dei moduli preservate. Questa correzione impedisce il riuso di bundle diversi dal documento nuovo; non dimostra che la cache fosse la causa della segnalazione dell’utente.
- Il form senza action/method espliciti poteva usare GET in assenza di JS. OraPOST verso rotta nominata, redirect303 e feedback server soltanto dopo salvataggio; errori e dati ripopolati con escaping e whitelist.
- Interesse selezionato, piano eMaxFull conservati nel riepilogo canonico e nella coppia lead/attivitàCRM. Consenso validato sul server. Richieste identiche entro10minuti ritrovano una coppia già salvata, senza duplicarla; nuove richieste distinte restano ammesse. Scritture pubbliche serializzate con lock, salvataggio transazionale, nessun nuovo schema.

## Prove e stato corrente

Rilettura pubblica prima delle modifiche:28URL e105ancore conformi. Prova in sola lettura della versione online a1440/768/390:cinque pagine, clic menuModuli, tour e tastiera, prezzi/MaxFull e annullamentoPASS; nessun POST/erroreJS/HTTP. Non riprodotti il codice specifico dell’errore utente o una risposta origin con vecchio HTML: segnalazione mantenuta distinta, browser/messaggio esatto non disponibili.

`BASE-CONFERMA` sulla baseline1a2e5e1f:3browserNONPASS per falsa conferma riprodotta, zero scritture reali. `R1` sul primo candidato:190PHP/2039asserzioni,1NONPASS dovuto all’asserzione sul vecchio testo home. Backend nuovo senza altri rilievi in quella prova; browser non avviati dopo il fallimento. Runtimes effimeri rimossi; diagnostici conservati. Questi risultati non vengono sommati né presentati come qualifica finale.

R2 sul commit946028fd:192PHP/1994asserzioni,2NONPASS per middleware di contesto ancora dipendenti dalla sessione nella rotta asset resa senza stato; browser non avviati. Corretta esclusione soltanto per GET delle risorse pubbliche. Revisione indipendente ha inoltre individuato reset dopo errore nativo che ripristinava i valori precedenti: rimossi i default solo dopo conferma valida e aggiunta prova errore→correzione→salvataggio→campi vuoti.

Qualifica finale prevista sul commit esatto: otto suitePHP (sito/CRM, ISTAT, tenancy e barriereQuestura), quattro specbrowser con sei pagine, risorse/link/ancore, percorsoHome→Funzionalità→Ampliamenti→Prezzi→MaxFull→contatti, accesso diretto/reload/indietro/avanti, demo, falsi successi, dati CRM e backoffice sintetico. SoloVM ufficiale, DB/filesystem effimeri e servizi finti. Seguono revisionePR, C5 con nuoviPREPARE/S0 e recuperi/custodie, QAonline identificabile e rimozione verificata, monitor900s. Nessuna trasmissione a enti o email reale autorizzata per le prove.

## Limiti esterni e dati mancanti

AccettazioneRoss1000/StayTour non attestata dal sito né dai double; credenziali, codici e abilitazioni restano requisiti del relativo servizio. Dati societari/PartitaIVA, informativa privacy, recapiti aggiuntivi e condizioni operativeMaxFull da confermare. Nessun recapito storico viene promosso a dato attuale senza prova. Tour con schermate autentiche disponibile; nessun video inventato.
