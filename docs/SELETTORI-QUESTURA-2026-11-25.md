# Selettori Questura — 25 novembre 2026

## Obiettivo e perimetro

Richiesta esplicita del titolare: correggere il menu scuro di Profilo Alloggiati Web e Modalità invio Questura in locale, Git/GitHub e online, prima di rifare il capitolo video. Rischio basso, singolo componente Blade condiviso da modifica anagrafica e configurazione operativa.

## Modifica

`resources/views/struttura/questura-profilo.blade.php`: ai due select aggiunti `data-ui="select-search"` e `data-min-search="10"`, usando l’inizializzazione standard già caricata nel programma. Le liste di due e tre opzioni non richiedono ricerca. Nessuna modifica ai valori, nomi dei campi, autorizzazioni, validazione o invii. Confronto del sorgente: rimuovendo i soli nuovi attributi si ottiene esattamente il file precedente.

## Evidenze

VM ufficiale Lima `schedine-sviluppo-interno`, copia privata del candidato, runtime attestato da `tests/Isolation/run.py`, risorse sintetiche e rete confinata. Baseline `5b7ee42ab81cf799a6a7cf9ee1de3a9fa3fff017`.

- `run.py --phpunit tests/Feature/ConfigurazioneStrutturaAllineataTest.php tests/Feature/QuesturaAppartamentoTest.php tests/Feature/StrutturaAuthorizationTest.php`: 39 test, 336 asserzioni positivi, 64,50 MB.
- Build asset, GEO immutabile e 23 rifiuti di override positivi.
- Primo browser arrestato ordinatamente: il nuovo test cercava la vecchia etichetta Entra, mentre il login usa Accedi. Allineato il selettore della sola prova, nessuna modifica ulteriore al codice applicativo. Esito interrotto 130 conservato.
- R2 non positivo: mancavano nel test le due conferme di accesso previste dal programma. R3, con flusso completo e fixture proprietario sintetico: tre prove positive in 35,3 secondi su 1440, 768 e 390 px; apertura delle cinque opzioni, selezione, tastiera, assenza di sfondo nero, overflow, errori JavaScript e scritture.
- Recupero codice/Git/cache positivo in quattro interruzioni sintetiche e passaggio completo. Prova confinata nella VM; non è un ripristino del database operativo.

Le evidenze grezze private sono in `/private/tmp/questura-ui-20261125`; i test e la fixture riproducibili sono nel repository. Nessuna credenziale produttiva nei test o nel rapporto.

## Rilascio e limiti

Stato: PUBBLICATO E VERIFICATO online, commit `1d643d21f4d07c6e8f5464e776eab83862e500ce`, [PR59](https://github.com/joomlu/Schedinedinotifica/pull/59) integrata. Locale e main GitHub corrispondenti; checkpoint fresco Git/viste integro, ambiente e altre cache invariati, FPM attivo, barriera aperta e checkout servito pulito. Rilascio protetto da systemd concluso con successo in 7,635 secondi di runtime del servizio (non misura separata del disservizio). Invalidata sola cache Blade. Nel Chrome del proprietario demo verificati entrambi i menu aperti con grafica chiara, due profili e tre modalità; nessuna scelta operativa diversa salvata durante il controllo UI. Nessuna migrazione, modifica `.env`, dato operativo, credenziale, servizio di trasporto o invio. BASELINE IN AUDIT invariata.


La successiva registrazione del capitolo usa solo il proprietario demo e i tre hotel. Logout e nuovo login provati, richiesta iniziale di selezione osservata, salvataggio anagrafica Bellaria confermato e cambio rapido Igea Marina → Rimini verificato. Il selettore superiore porta al cruscotto dell’hotel scelto; non conserva necessariamente la pagina Dati struttura. Nessun invio a Questura/Ross1000, cambio di credenziali o dato reale per il video.
