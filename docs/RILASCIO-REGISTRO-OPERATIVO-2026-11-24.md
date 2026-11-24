# Registro operativo e logout — rilascio del 24 novembre 2026

**Pubblicato e verificato nello scope delle due correzioni. BASELINE IN AUDIT invariata.** Revisione applicativa `a4282df91bd978b9076ae82104f31546a5b9be4f`, [PR56](https://github.com/joomlu/Schedinedinotifica/pull/56) integrata dopo autorizzazione specifica del titolare. Il consuntivo documentale successivo conserva lo stesso codice applicativo.

Il logout GET usa la chiusura operativa del controller e mantiene il ritorno al login; il POST conserva il comportamento precedente. Il registro attribuisce le operazioni alla struttura corrente autorizzata, con verifica della membership anche per amministratori e proprietari senza struttura fissa. Gli ID arbitrari inviati nel corpo della richiesta non determinano il tenant del log. Due file applicativi modificati; cinque file di test nuovi o aggiornati.

Nella sola VM ufficiale, con dati sintetici e rete confinata: **168 test PHP / 2.147 asserzioni**, tre prove Chromium a 1440/768/390 con route effettivamente in cache, 15 guardie, 23 rifiuti degli override e build/GEO positivi. Controllati GET/POST logout, chiusura del solo accesso dell'utente, quattro ruoli, tenant estraneo, sessioni/password e blocco dei trasporti. I fallimenti intermedi sono stati conservati e risolti: una destinazione GET cambiata dal primo candidato, fixture e aspettative legacy da allineare al divieto esistente di escalation. Nessun permesso applicativo allentato.

Qualificato il recupero di codice, Git e cache con quattro interruzioni sintetiche e un passaggio completo; qualificato separatamente il callback del guardiano systemd. Primo rilascio fermo prima delle mutazioni per il contesto MariaDB della unità: baseline riaperta. Seconda finestra distinta con contesto esplicito della sola query tecnica, checkpoint fresco verificato indipendentemente e guardiano: esito positivo, intervallo del journal 7,874 secondi. Il dato non misura separatamente l'indisponibilità HTTP.

Dopo l'installazione: sette impronte servite identiche al candidato collaudato, Git pulito, cache delle route rigenerata, ambiente e altre cache invariati, FPM dedicato attivo e barriera aperta. **33 navigazioni pubbliche a tre larghezze senza problemi rilevati; sette campioni e 35 sonde HTTPS in circa 181 secondi senza anomalie.** Sono verifiche circoscritte, non un monitoraggio prolungato o una prova autenticata su account reali.

Il controllo aggregato degli accessi produttivi, in sola lettura, non ha rilevato ruoli attivi sconosciuti, operatori privi di struttura esistente o proprietari privi di anagrafica. Nessun nominativo, credenziale o dato ospite estratto.

Non sono state applicate migrazioni, modifiche di configurazione applicativa, correzioni dei dati reali o trasmissioni agli enti. Il logout GET conserva i limiti della compatibilità preesistente; il registro rimane limitato ai metodi, prefissi ed esiti previsti e non costituisce un audit generale o transazionale. Nessuna nuova attestazione di sicurezza esaustiva o accettazione degli enti.

Il lavoro documentale preesistente del titolare è stato preservato ed escluso dal consuntivo selettivo.
