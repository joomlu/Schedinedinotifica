# Verifica formulario Titoli — 14 ottobre 2026

> Rapporto storico dell’audit iniziale, precedente all’autorizzazione alle correzioni. I difetti riprodotti e il verdetto di allora sono conservati; la verifica corrente e il rilascio sono nel [consuntivo definitivo Titoli](CORREZIONE-TITOLI-2026-10-14.md).

**STATO: PARZIALE, formulario NON CONFERMATO. BASELINE IN AUDIT.**

Obiettivo: audit di `/titolo`, numero/origine, abbreviazioni, descrizioni, stato, azioni, ricerca/paginazione e isolamento della struttura. Nessuna correzione applicativa autorizzata. Rischio medio: prove mutanti soltanto su fixture sintetiche effimere nella VM ufficiale; browser online soltanto letture e apertura/chiusura modali senza invio. Il prompt precedente richiamato dall’utente non è disponibile nella conversazione corrente: richiesto tramite domanda asincrona, nessuna conferma completa sostitutiva.

## Conteggio e provenienza

Browser autenticato Super Admin, contesto HOTEL GIOIELLA, `https://schedinedinotifica.tanggo.software/titolo`: percorse tutte le pagine 1–9 mediante collegamenti visibili. Prime otto pagine da10 righe, nona da2: **82 record con82 ID distinti**, insieme ID1–82, tutti visualizzati Attivo. Nome/Descrizione/Stato/Azioni presenti; Modifica/Elimina disponibili su tutte le righe. Non trovati nomi identici duplicati nell’insieme osservato.

Seeder corrente: **80 elementi**, tutti presenti online con medesimi nome e descrizione. Due ulteriori record: ID1 `Sig.s`/`Signore`, ID4 `Sigg.`/`Signori`, assenti dal seeder. `Sig.`/`Signore` è distinto, ID17; `Sig.ra`ID2, `Sig.na`ID3, `Sig.re`ID18. **82 non equivale a82 titoli della base originale.** La corrispondenza degli80 dimostra compatibilità col seeder, non una catena storica di creazione. Non disponibili evidenze sufficienti di autore/data/importazione dei singoli record; non classificati i due extra come fittizi, errori o nuove creazioni. Nessuna cancellazione/normalizzazione.

Inventario per pagina (nome nell’ordine osservato):

| Pagina | Nomi |
|---|---|
|1|Amm.; Arch.; Avv.; Barone; Baronessa; Baronetto; Cap.; Cav.; Cav. Lav.; Cav. Uff.|
|2|Ch.mo; Cliente; Col.; Comm.; Cons.; Cons.re; Conte; Contessa; Dama; Dir.|
|3|Ditta; Don; Dott.; Dott.ssa; Dr.; Duca; Duchessa; Egr.; Emin.; Fam.|
|4|Fra; Gen.; Gent. Sig.; Gent. Sig.ra; Gent.ma; Gent.me; Gent.mi; Gent.mo; Geom.; Grand'Uff.|
|5|Ill.mo; Ing.; Madre; Mag.; Marchesa; Marchese; Min.; Mons.; On.; Ospite|
|6|Padre; Past.; Per. Agr.; Per. Ind.; PhD; Pref.; Principe; Principessa; Prof.; Prof.ssa|
|7|Quest.; Rag.; Resp.; Rev.; Rev.mo; Ric.; Ric.ssa; Sen.; Serg.; Sig.|
|8|Sig. e Sig.ra; Sig.na; Sig.ra; Sig.re; Sig.s; Sigg.; Sind.; Sir; Soc.; Spett.le|
|9|Suor; Ten.|

Descrizioni degli80 in [estrazione statica](evidenze/titoli-2026-10-14/seeder-statico.json); quelle dei due extra sopra. `Societa` senza accento coincide col seeder; `Dr.`/`Doctor` e `Dott.`/`Dottore` sono forme distinte, non duplicate automaticamente. `Sig.s` merita verifica della fonte, nessuna interpretazione inventata.

## Riscontri browser e ricerca

Conteggi iniziale/finale, colonne e nove pagine verificati online. Modale Modifica su `Ing.` mostra Nome `Ing.`, Descrizione `Ingegnere`, switch Attivo selezionato, Chiudi e Salva; apertura/chiusura PASS. Nuovo Titolo mostra stessi campi vuoti e Attivo selezionato, chiusura PASS. Nessun Salva/Elimina eseguito online.

`Sig.` restituisce11 risultati: include `Cons.re` dalla descrizione Consigliere e `Mons.` da Monsignore. `Dott.ssa` restituisce9: Baronessa, Contessa, Dott., Dott.ssa, Duchessa, PhD, Principessa, Prof.ssa, Ric.ssa. `Ingegnere` restituisce un solo `Ing.`. Il codice divide le abbreviazioni sui punti e applica OR ai termini, quindi `Dott.ssa` corrisponde a `dott` **oppure** `ssa`, anche nelle descrizioni. Ricerca volutamente larga secondo implementazione; pertinenza per abbreviazioni composte non attestabile come requisito soddisfatto. Query conservata nei collegamenti di paginazione (`q=Sig.&page=2`); nuova ricerca elimina `page`, `withQueryString()` conserva q. Il filtro non aggiunge alcuna struttura.

## Difetti riprodotti e sicurezza

Navigazione filtrata online verificata effettivamente: `Sig.` pagina1 dieci righe → collegamento2 → pagina2 con solo `Sigg.` e contatore11–11di11; q preservata. Pulisci ripristina pagina1/82risultati; infine riaperta pagina7 senza query, come al preflight. Contesto struttura invariato.

1. **Isolamento richiesto non soddisfatto.** `Titolo` non implementa scope tenant e non ha `struttura_id` nelle migration applicative. Controller globale e binding globale; route nel gruppo auth, senza policy di ruolo/struttura specifica. In prova HTTP, struttura_user A crea un titolo; struttura_user B lo ritrova cercando e modifica il medesimo ID. Non è sufficiente il selettore in testata. La natura globale del catalogo già segnalata nel Maestro è incompatibile col requisito attuale di personalizzazioni isolate. Nessun exploit mutante tentato sui dati online.
2. **Disattivazione dal formulario fallisce.** Checkbox deselezionata omette `attivo`; update mantiene `$titolo->attivo`, store applica true. Prova HTTP PUT con payload equivalente al formulario: record attivo resta attivo. Valore esplicito `0` accettato dal backend, quindi l’errore riguarda il contratto formulario/server. Riattivazione con1 passa; badge Disattivo su fixture0 verificato.
3. **Origine storica non attestata.** Seeder `updateOrCreate` può riscrivere descrizioni e riattivare personalizzazioni, senza marcatore di origine. Non eseguito: nessuna deduzione sull’ultima esecuzione reale. Nome non ha validazione unique nel controller: politica sui duplicati non definita per questo audit.

Anonimo rimandato al login, CSRF errato419, nome obbligatorio/max50, descrizione max150 e booleano invalido respinto: prove PASS. Creazione sintetica con0, aggiornamento con1, cancellazione verso Cestino e ID inesistente404 PASS. Ripristino/purge, matrice completa ruoli, seeder eseguito e browser mutante su fixture non eseguiti: non promossi a verificati. Lo stato attivo nei selettori non è uniforme staticamente: CustomerController/CustomerImport filtrano attivo; SchedinaController preleva tutti i titoli. Nessun cambiamento di questi flussi.

## Esecuzione riproducibile

HEAD locale `fdd03d1fadf1d903c5a821754b525c6c185f494e`, branch main. VM `schedine-sviluppo-interno`, PHP8.3.33/MariaDB11.8.9, namespace senza rete, schema corrente del runner e DB effimero senza dati reali. Stato VM inizialmente negato dal sandbox; nuova esecuzione esterna autorizzata PASS, non guasto della VM.

Comandi:

```sh
python3 scripts/development/ambiente-interno.py stato
python3 scripts/development/ambiente-interno.py sincronizza
python3 scripts/development/ambiente-interno.py test tests/Feature/TitoliFormularioAudit.php
```

Sincronizzazione conserva versioni precedenti in `storia-sviluppo/1791932459694614085`; nessuna sovrascrittura manuale. Runner: attestazione processi/DB/HTTP e riconnessione,23 override pericolosi respinti primaDDL, build/GEO PASS. **4 test/38 asserzioni,2 PASS e2 FAIL**, nessun errore PHPUnit. I due FAIL dimostrano stato e tenancy, aspettative non alterate per ottenere verde. [Log integrale](evidenze/titoli-2026-10-14/prova-isolata.log); risorse temporanee fermate e rimosse dal runner. Nessuna suite globale implicita. HEAD produttivo non riattestato: letture online e prove locali sono evidenze distinte.

## Consegna e limiti

Modifiche: solo diagnostico esplicito `TitoliFormularioAudit.php`, report/evidenze e voce nel Maestro. Lavoro documentale preesistente preservato; nessuna operazione Git mutante, staging/commit/push. Nessuna regressione applicativa introdotta; rischi attuali: modifiche globali fra strutture, disattivazione inefficace, provenienza e policy dei duplicati non attestabili. Prossimo passo: recuperare il protocollo precedente e autorizzare correzioni circoscritte per catalogo privato e switch, con conservazione degli82 record e successive prove complete. Nessuna estensione automatica alla procedura Gruppi né deploy.

CODICE APPLICATIVO MODIFICATO: NO; TEST MODIFICATI: SI; DOCUMENTAZIONE MODIFICATA: SI; CONFIGURAZIONE MODIFICATA: NO; SCHEMA/DATI REALI MODIFICATI: NO; SCHEMA/DATI EFFIMERI: SI; TEST ESEGUITI: SI; COMMIT: NO; PUSH: NO; DEPLOY: NO; TRASMISSIONI ESTERNE A ENTI/MESSAGGI: NO. MAESTRO: AGGIORNATO. Conferma del formulario: NO.
