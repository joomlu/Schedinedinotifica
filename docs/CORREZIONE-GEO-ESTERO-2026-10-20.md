# Correzione GEO estero — Schedina e Web Check-in, 20 ottobre 2026

## Stato: PARZIALE — difetto confermato online, correzione proposta non pubblicata
Ramo: codex/web-checkin-testi-palette-2026-10-20, PR #4; estensione del candidato f518345c050abc66c19c827be7133cc8dedff729.
BASELINE IN AUDIT invariata. La palette completa resta inclusa nel candidato, non dichiarata online.

## Evidenza reale e causa
Ispezione in sola lettura del modulo pubblico già aperto dall'utente, senza cambiare campi o inviare il form.
Il selettore della nazione mostrava il valore numerico 156. Il campo invalido letto dal DOM era oa_city, con messaggio «Il campo città anagrafica è obbligatorio.»
Il controller condiviso SchedinaController imponeva required a oa_city e or_city per qualunque nazione. WebCheckinController richiama validateSchedinaRequest anche per Conferma dati e per i pendenti. Difetto quindi presente nella validazione condivisa, non limitato alla nuova navigazione.
Il formulario condiviso passava all'inizializzazione GEO il valore grezzo di old input o schedina. In caso di errore, l'ID inviato diventava sia value sia etichetta. Ora passa dal servizio CustomerWizardValidation::initial esistente per risolvere ID/nome e modalità Italia/Estero.

## Regola verificata su fonte ufficiale
Manuale Alloggiati Web:
https://alloggiatiweb.poliziadistato.it/portalealloggiati/Download/Manuali/MANUALEALBERGHI.pdf
Consultato il 20 ottobre 2026, sezioni 3 e 12, pagine PDF 8 e 30–33: nascita estera identificata dallo Stato; comune e provincia italiani nel tracciato restano vuoti. Cittadinanza è un campo separato. La residenza non compare nei campi del tracciato Questura descritto nel manuale. Questo non elimina eventuali requisiti statistici/fiscali di altri moduli; nessuna modifica agli export ISTAT/Questura o ai loro controlli.

## Modifiche del candidato
- oa_city e or_city richiesti solo se la nazione è italiana, usando il controllo di catalogo già esistente. Nazioni e cittadinanza continuano a essere validate.
- Tipo via/indirizzo/civico obbligatori solo per residenza italiana; facoltativi per residenza estera. Nessun valore inventato.
- Quattro istanze GEO del formulario Schedina, titolare e componenti, risolvono i valori iniziali tramite il servizio esistente. Le nazioni sono mostrate col nome anche dopo errore o ripresa.
- Dati geografici esteri ulteriori raccolti sotto «Altri dati esteri (facoltativi)», chiuso inizialmente. Stesso componente GEO, con opzione predefinita disattivata per gli altri utilizzatori; nessun secondo sistema GEO.
- Un CAP estero iniziale non avvia più resolve del catalogo italiano.
- Nel modulo pubblico il messaggio di errore viene associato al controllo abilitato col nome corrispondente; l'eventuale sezione facoltativa viene aperta. Navigazione, save e componenti conservati.
- Testi informativi e indicazioni di obbligatorietà allineati.

## Verifiche eseguite
- Lettura sorgenti di controller Schedina/Web Check-in, validazione GEO/clienti, normalizzazione componenti, formulario e componente GEO.
- DOM pubblico reale: errore oa_city e nazione numerica confermati. Nessun salvataggio o dato reale modificato in questa attività.
- node --check geo-review/resources/js/ui/geo-italia.js: PASS sintattico.
- git diff --no-index --check geo-before geo-review: nessun errore whitespace, exit 1 per differenze.
- Revisione dei diff applicativi/test eseguita.

## Prove predisposte, NON eseguite
Sei nuove regressioni PHP: Schedina estera con soli Stati e senza indirizzo; comuni italiani ancora obbligatori; ID/nome/modalità GEO; conferma pubblica estera; errore e ripresa con etichetta nazione; salvataggio parziale estero e ripresa.
PHP, Blade, PHPUnit, browser applicativo, build Vite e C5 non eseguiti: la sessione non dispone di PHP, limactl e VM ufficiale. Nessun aggiramento di TEST_ISOLATION_REQUIRED, nessun DB reale usato per test.
Prima di integrare eseguire nella VM ufficiale le suite SchedinaFlowValidationTest, WebCheckinHtmlTest, CustomerWizardTest, WebCheckinCircuitoTest e gli spec HTML/circuito con fixture sintetiche. Verificare anche passaggi Italia→Estero→Italia, CAP estero, componenti aggiunti/rimossi, errori nella sezione facoltativa, due tenant e larghezze 1440/768/390. Gli esiti precedenti non certificano questo nuovo candidato.

## Ricezione email
L'utente ha confermato in questa conversazione la ricezione in Gmail dell'unico invio della richiesta 134 delle 15:01:01 Europe/Rome. È una conferma utente, non un'ispezione autonoma della casella. Nessuna nuova email inviata in questa attività. Quell'invio precede queste modifiche e usa l'applicativo precedente.

## Tenancy, rischio e chiusura
Rischio MEDIO per regole e UI condivise. Nessuna modifica a schema, token, autorizzazioni, scadenze, trasporti, serializer Questura o contratti ISTAT. Campi esteri facoltativi già presenti conservati; nessuna migrazione o pulizia dei dati. Prove dinamiche negative su due tenant e regressioni ancora pendenti. Prossimo passo: collaudo nel runtime ufficiale e C5, non pubblicazione diretta.

CODICE MODIFICATO: SI. TEST MODIFICATI: SI. DOCUMENTAZIONE MODIFICATA: SI.
CONFIGURAZIONE MODIFICATA: NO. SCHEMA/DATI REALI MODIFICATI: NO.
TEST FUNZIONALI DEL CANDIDATO ESEGUITI: NO. CONTROLLI STATICI: SI.
COMMIT/PUSH: SI sul solo ramo della PR. DEPLOY: NO. TRASMISSIONI ESTERNE: NO.
