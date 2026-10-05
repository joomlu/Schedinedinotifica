# Separazione definitiva Superadmin / Admin — 4 ottobre 2026

**SEPARAZIONE SUPERADMIN / ADMIN VERIFICATA — CRM E PROFORME SUPERADMIN ONLY; PAGAMENTI, LICENZE E STORICO ADMIN READ-ONLY E LIMITATI ALLE PROPRIE STRUTTURE.**

## Audit prima delle modifiche

Ruoli reali nel modello User: super_admin, admin, proprietario, struttura_user. Il ruolo operativo del personale è distinto. Autorizzazione HTTP: auth e middleware Ruolo, alias ruolo nel Kernel, che confronta user.ruolo con i ruoli ammessi. AuthServiceProvider non registra Policy concrete né Gate applicativi per queste sezioni; non è stato introdotto un nuovo sistema di permessi.

Relazione amministrativa: User::proprietariGestiti → proprietari.admin_id → struttura.proprietario_id. Le licenze hanno anche admin_id e proprietario_id, ma questi campi ridondanti non devono autorizzare una licenza riferita a una struttura esterna. Gli scope operativi AppartieneAStruttura e il middleware ImpostaStrutturaCorrente restano invariati. La loro compatibilità legacy con strutture senza proprietario non viene estesa ai dati economici: per la consultazione amministrativa serve l'assegnazione effettiva tramite proprietario.

Aree censite: route superadmin/admin per CRM, Proforme, Proprietari, Strutture, Pagamenti; licenze e storico sono sezioni della pagina Pagamenti e della scheda struttura/proprietario. Non esiste un CRUD Pagamento indipendente: lo stato economico deriva da Struttura, LicenzaAssegnazione e, nel circuito Superadmin, Proforme. Storico operativo clienti/Questura non è lo storico amministrativo e non è stato modificato.

51 route amministrative CRM/Pagamenti/Proforme censite, con metodi e middleware in inventario-route.json. routes/api.php espone utente e GEO, non API amministrative di queste aree; nessun endpoint Livewire pertinente trovato. Le richieste AJAX alle route web sono sottoposte agli stessi middleware.

Falle riscontrate: route Admin CRM/Proforme attive; scrittura licenze consentita; licenze autorizzate con OR su admin_id/proprietario/struttura; Proforme presenti nello storico Admin; campi commerciali e sincronizzazione licenza nella modifica struttura; scorciatoia Nuova proforma; cestino utilizzabile per ripristinare/eliminare licenze; catch-all capace di raggiungere view amministrative senza i relativi controller.

## Modifiche e comportamento

- Route Admin CRM e Proforme, scritture admin.pagamenti.licenze.store/update/destroy e admin.strutture.servizio: ulteriore middleware ruolo:super_admin. Insieme al gruppo ruolo:admin le route legacy sono negate con 403. Il Superadmin usa le proprie route superadmin.*, rimaste invariate.
- Nuovo scope locale LicenzaAssegnazione::perStruttureAdmin: verifica struttura.proprietario.admin_id. Riutilizzato da lista, stampa/dettaglio licenza e storico proprietario. Licenze senza struttura o con admin_id incoerente non concedono accesso.
- Proforme rimosse dai dati di Pagamenti/storico e schede Admin, non soltanto nascoste nell'HTML. Le Proforme restano nel circuito Superadmin. Lo storico Admin conserva le licenze e i relativi stati/importi delle strutture assegnate.
- Scheda struttura Admin: condizioni commerciali consultabili, campi commerciali non inviabili; payload manipolato respinto 403. Il salvataggio operativo non sincronizza licenze e preserva esattamente attiva, piano, scadenza_servizio e stato_pagamento, anche se nulli. Nuove strutture Admin non attivano automaticamente una licenza: restano non attive e da pagare finché il Superadmin le abilita.
- Eliminazione di struttura/proprietario con riferimenti amministrativi protetti respinta: non può essere usata per alterare indirettamente le licenze.
- Cestino Admin limitato alle strutture assegnate; snapshot amministrativi globali esclusi. Licenze archiviate proprie consultabili ma ripristino/eliminazione negati e pulsanti nascosti. Le azioni sui normali record operativi propri restano disponibili.
- Sidebar Admin senza CRM/Proforme; rimosse le scorciatoie Proforme dalle schede. Dashboard economica usa l'assegnazione effettiva della struttura. Catch-all non renderizza direttamente view admin/*, superadmin/* o shared/*: si passa dalle route/controller autorizzati.

Classi middleware/Gate/Policy modificate: **nessuna**. Riutilizzato Ruolo nelle dichiarazioni delle route; aggiunto uno scope locale al modello delle licenze. Nessuna modifica globale agli scope dei moduli operativi.

## Matrice finale

### SUPERADMIN

Controllo | Esito
---|---
CRM visibile | SI
CRM amministrabile | SI
Proforme visibile | SI
Proforme amministrabile | SI
Pagamenti globali | SI
Licenze globali | SI
Storico globale | SI

### ADMIN

Controllo | Esito
---|---
CRM nascosto | SI
CRM backend bloccato | SI
Proforme nascosto | SI
Proforme backend bloccato | SI

### PAGAMENTI ADMIN

Controllo | Esito
---|---
proprie strutture visibili | SI
altre strutture escluse | SI
sola lettura | SI
create bloccato | SI
update bloccato | SI
delete bloccato | SI

### LICENZE ADMIN

Controllo | Esito
---|---
proprie strutture visibili | SI
altre strutture escluse | SI
sola lettura | SI
modifica bloccata | SI
delete bloccato | SI

### STORICO ADMIN

Controllo | Esito
---|---
proprie strutture visibili | SI
altre strutture escluse | SI
sola lettura | SI

### SICUREZZA

Controllo | Esito
---|---
isolamento struttura | SI
URL manipulation protetta | SI
ID manipulation protetta | SI
GET protetto | SI
POST protetto | SI
PUT/PATCH protetto | SI
DELETE protetto | SI

### REGRESSIONI

Controllo | Esito
---|---
operatività ADMIN invariata | SI
operatività SUPERADMIN invariata | SI
Clienti invariato | SI
Import Clienti invariato | SI
Schedina invariata | SI
Componenti invariato | SI

API amministrative dedicate: **NON PRESENTI**. AJAX sulle route web: **SI, protetto dagli stessi middleware**. PUT è verificato sulle route esistenti; PATCH amministrativo non è registrato e non fornisce un percorso alternativo autorizzato. I parametri ID sono risolti esplicitamente dai controller con query limitate; non è stato introdotto binding implicito non autorizzato.

“Operatività invariata” riguarda le normali funzioni sulle strutture assegnate e le funzioni Superadmin esistenti; esclude naturalmente i privilegi amministrativi Admin rimossi intenzionalmente, la creazione di licenze automatica e i percorsi diretti non autorizzati alle view.

## Prove eseguite e limiti

Harness: /tmp/ruoli-20261004/probe.php, bootstrap.php, esiti.log. Fixture con due Admin, un Superadmin, due proprietari, Hotel A e Hotel B, licenza propria, licenza esterna con admin_id/proprietario_id volutamente ingannevoli, licenza senza struttura, Proforma e snapshot cestino. Database locale InnoDB, transazione esterna e rollback in finally. Impronte prima/dopo identiche sulle tabelle controllate; nessuna fixture residua. I contatori AUTO_INCREMENT possono avanzare nonostante rollback.

- 110 verifiche combinazione route/metodo: tutte positive. Ruolo eseguito sulle dichiarazioni reali; tutte le route Superadmin ammettono Superadmin e negano Admin; tutte le route Admin vietate negano Admin, prima delle action.
- Controller reali: lista licenze solo Hotel A; stampa propria disponibile, ID Hotel B e licenza senza struttura negati. Manipolazione conto_struttura_id/admin_id/struttura_id non amplia lo scope; storico del filtro esterno vuoto, proprio disponibile.
- Render reali Blade: Pagamenti Admin, struttura, proprietario, cestino, dashboard Admin e Pagamenti Superadmin. Nessun dato Proforma Admin, nessun pulsante Nuova proforma, nessun campo commerciale inviabile; menu Superadmin CRM/Proforme presente.
- Aggiornamento operativo reale della struttura Admin superato: modifica anagrafica applicata, licenza e campi commerciali identici. Rilevato e corretto durante la verifica il fallback che trasformava valori commerciali nulli.
- Tentativi su struttura esterna, payload commerciale, eliminazione indiretta con licenze, ripristino/eliminazione licenza dal cestino e ID cestino esterno: respinti senza scritture.
- Accessibilità per ruolo delle route Clienti, Import Clienti, Clienti importati, Liste e export e nuova schedina verificata; i relativi controller, view, servizi e route non sono stati modificati.
- Superadmin: consultazione globale di entrambe le licenze, menu CRM/Proforme, autorizzazione dei metodi di scrittura esistenti confermati. I controller Superadmin non sono stati modificati; non sono state ripetute tutte le operazioni commerciali di creazione/fatturazione né inviati messaggi.
- Browser con sessione Admin reale: Pagamenti/Licenze disponibili in consultazione; menu senza CRM/Proforme; URL /admin/crm e /admin/proforme mostrano 403. Nessuna modifica di ruolo o scrittura amministrativa tramite browser.
- Tre tentativi diretti al catch-all (admin/crm/index, superadmin/proprietari/proforma-show, shared/proforme/index) respinti con 403.

TEST_ISOLATION_REQUIRED non aggirato. Queste sono prove dirette di middleware, controller e database, non una suite HTTP PHPUnit né un test completo di ogni middleware di sessione/CSRF. I permessi di scrittura Superadmin sono verificati sull'autorizzazione e sull'invarianza del codice; non è stata eseguita una nuova fatturazione completa. Nessuna prova concorrente. Le prime esecuzioni hanno richiesto correzioni delle fixture e del contesto route dell'harness; l'ultima esecuzione completa è positiva.

## File modificati durante questa task

- app/Http/Controllers/Admin/PagamentiController.php
- app/Http/Controllers/Admin/ProprietariController.php
- app/Http/Controllers/Admin/StruttureController.php
- app/Http/Controllers/CestinoController.php
- app/Http/Controllers/HomeController.php
- app/Models/LicenzaAssegnazione.php
- resources/views/cestino/index.blade.php
- resources/views/layouts/sidebar.blade.php
- resources/views/shared/proprietari/form-panels.blade.php
- resources/views/shared/strutture/admin-form-panels-admin.blade.php
- resources/views/superadmin/pagamenti/index.blade.php
- routes/web.php
- docs/separazione-superadmin-admin-2026-10-04.md — questo report.

Tutti gli altri file della baseline iniziale sono invariati. Le modifiche pregresse visibili nel working tree appartengono alle task precedenti. Import Clienti, staging, Clienti, Liste e export, Schedina, Componenti, Questura, GEO, Notifiche e gli altri moduli operativi protetti non sono stati modificati in questa task.

Evidenze: /tmp/ruoli-20261004/route-esiti.json, inventario-route.json, esiti.log, baseline.json, copie iniziali in files/ e rendering in runtime/. Controlli conclusivi richiesti: git diff --check; git status --short --untracked-files=all; git diff --stat; git diff --name-only. Nessun commit, push, deploy, reset o clean.
