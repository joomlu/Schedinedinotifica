# Correzioni schedine, stampa e comandi cliente — 26 novembre 2026

## Mandato e perimetro

Richiesta del titolare: correzioni in locale, Git/GitHub e online. Codice schedina cliccabile e matita verso lo stesso modulo completo modificabile; stampante verso la schedina A4 con titolare e componenti; tre azioni cliente senza sottolineatura e con aspetto di pulsanti coerente con la palette esistente. Rischio medio circoscritto alla nuova pagina contenente dati personali. Nessuna migrazione, modifica di dati operativi, configurazione, credenziale o invio agli enti.

## Implementazione

Cinque sorgenti applicativi: `SchedinaController`, route web, elenco schedine, nuovo Blade `schedina/print`, stile delle azioni di `customers/print`. La matita e il salvataggio esistenti sono conservati; il codice usa la medesima route di modifica. La stampa legge gli snapshot della schedina e dei componenti, senza rigenerare o consolidare la ricevuta fiscale. Pagina autonoma con azioni nascoste su carta, formato A4 verticale, titolare prima dei componenti, prosecuzione su ulteriori A4 per gruppi numerosi. I dati testuali sono sottoposti all’escaping Blade. Filtro esplicito per struttura su schedina, componenti e camere; cache privata disabilitata. Middleware e scope tenant esistenti conservati.

## Prove e risultati

VM ufficiale Lima `schedine-sviluppo-interno`, copia privata `/home/collaudo.guest/schedina-ui-5EUlynhy/repo`; avviatore attestato, namespace, MariaDB e filesystem effimeri, sole fixture sintetiche.

Selezione PHP: `tests/Feature/SchedinaPrintTest.php tests/Feature/SchedinaNavigationTest.php tests/Feature/SchedinaFlowValidationTest.php`. R2: 27 test / 189 asserzioni positivi, inclusi modifica e salvataggio nota, stampa titolare/componente, nessuna fuga di componenti di altro hotel, dinieghi lettura/modifica/stampa da altra struttura, rifiuto anonimo ed escaping. Build/GEO e 23 rifiuti di override positivi.

R1: 25 prove positive e due fallimenti perché il nuovo Blade non tracciato era escluso dalla copia effimera. Incluso nell’indice della sola copia privata, senza indebolire il launcher. Browser R2: tre fallimenti del confronto URL della fixture, che presumeva un codice non riconciliato con la rinumerazione effettiva; corretta la sola prova per usare ID e codice persistiti. R3 e R4 interrotte ordinatamente durante l’attesa del selettore: la fixture ereditava righe di storico con paginazione e il codice veniva riconciliato all’apertura dell’elenco. Fixture resa dedicata e prova collegata all’ID della riga e al codice visibile; nessuna modifica applicativa aggiuntiva. R5 positiva: 27 test / 189 asserzioni e tre browser a 1440/768/390 con route in cache; codice e matita, nuova stampa, comandi cliente, zero overflow/errori JavaScript/richieste HTTP mutanti dopo il login. Non equivale ad assenza globale di scritture da GET: la rinumerazione preesistente resta invariata. Tre PDF prodotti identici nel contenuto; campione renderizzato e ispezionato: una pagina A4, titolare e due componenti leggibili, nessun taglio. Diagnostici e tentativi conservati in `/private/tmp/schedina-ui-20261126`.

Recupero del rilascio qualificato nella VM: quattro interruzioni sintetiche (primo file, Git, cache assente, cache scritta) e percorso completo positivi. Ripristinati codice/Git, route e viste originali; sonde HTTP simulate nella prova di recupero, non equivalenti a una prova del sito reale.

## Stato e limiti

Candidato locale qualificato; pubblicazione non ancora eseguita al momento della preparazione del rapporto. Base locale/GitHub/server `5e7af5d1841eca322858008dfab7d1262693c956`, server pulito e FPM dedicato attivo. Lavoro documentale preesistente conservato ed escluso dallo staging selettivo. Rilascio con checkpoint fresco, barriera, recupero automatico e sola rigenerazione route/svuotamento viste; nessun intervento DB. BASELINE IN AUDIT invariata.
