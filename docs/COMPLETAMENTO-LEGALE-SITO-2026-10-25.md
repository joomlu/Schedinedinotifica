# Completamento legale e protezioni del sito — 25 ottobre 2026

**QUALIFICA R17 PASS; nuovo rilascio da eseguire. BASELINE IN AUDIT invariata.**

Il rilascio4a85dea2 è concluso con S0 144,710s, RUN253,001s, zeroDDL; QA sei pagine×1440/768/390, tre richieste/attività sintetiche rimosse e HMAC66 conforme. Monitor server918,765s eMac920,509s PASS, zero nuovi errori Laravel/FPM. [Ricevuta separata](evidenze/sito-integrale-2026-10-25/rilascio-4a85dea2.json).

Il controllo dei requisiti ha rilevato l’esclusione CSRF del POST contatti e il rinvio al login dei percorsi assenti. Il nuovo candidato riattiva il middleware, inietta il token di sessione nel modulo e lo invia anche nel JSON; token vuoti/errati419 e nessuna scrittura. Feedback419 italiano e404 utile; rotte gestionali esplicite ancora protette. Nessuna modifica allo schema o alle funzioni fiscali/statistiche.

Dopo la comunicazione umana dei dati societari e l’incarico «hacela vos», aggiunte /note-legali, /privacy e /cookie con ragione sociale, sede, CF/PIVA, REA e PEC di Experience S.r.l. Coordinate bancarie e dati del bar esclusi. Collegamenti nel footer e accanto al consenso; copyright societario, sitemap e metadati di nove pagine. Nessun nuovo servizio esterno o tracciamento. Privacy limitata al sito commerciale; criteri di conservazione coerenti con la gestione delle richieste, nessun termine commerciale inventato.

Riferimenti primari consultati: [informativa e consenso](https://www.garanteprivacy.it/web/guest/home/docweb/-/docweb-display/docweb/8981258), [diritti](https://www.garanteprivacy.it/it/home/i-miei-diritti/diritti), [cookie](https://www.garanteprivacy.it/faq/Cookie). La redazione non attesta contratti/DPA dei fornitori né la gestione dei dati ospiti nel gestionale.

R11 rifiutato prima dell’avvio per nome fixture inesistente; R12:195PHP/2125asserzioni, due NONPASS (header valido automatico nel test JSON e ordine middleware del fallback). Corretto test con header esplicitamente errato e fallback nella fase effettiva di autenticazione. R13:195PHP/2136asserzioni e18/19browser; unico NONPASS del vecchio POST nativo senza token. R14:196PHP/2218asserzioni e18/19browser; salto alla sola ancora sulla stessa pagina non rileggeva il feedback. Adattatori corretti conservando asserzioni, timeout e protezioni. R15 sul commit a8127069:196PHP/2218asserzioni e19browser PASS; nove pagine,23rifiuti override, build/GEO e4408digest conformi. Esiti distinti, mai sommati. Nessuna prova Laravel sull’host.

Conservati su Mac401file diagnostici propri R1–R14 con confronto SHA e metadati prima del ritiro della copia VM. Ritirati soltanto derivati dei due recuperi propri conclusi dopo verifica completa; SQL, chiavi, originali e cifrati preservati. Una copia cifrata temporanea propria è custodita byte-identica su Mac ed Extra, oltre all’originale server, con metadati per ricostruzione. Nessun progetto o backup estraneo rimosso. Nuovo PREPARE/S0/QA/monitor richiesti per il correttivo: i PASS del primo rilascio non li sostituiscono.


## Revisione PR26 e qualifica R16

La revisione propria e il bot sul47cf2bfe hanno rilevato lo stessoP2: dopo il primo invio il reset svuotava anche il nuovo tokenCSRF. Escluso soltanto _token dal reset in entrambe le copieJS; nuova prova esegue due richieste distinte senza ricarica, verifica duelead e conserva il token dopo ciascun reset. R16 sulf873ed52:196PHP/2218asserzioni e20browserPASS, nove pagine×1440/768/390,23override/buildGEO/4408digest. R15 resta conservata separatamente e superata quanto a candidato. Nuovo rilascio ancora da eseguire.


## Verifica finale dei collegamenti e R17

Il dominio tanggo.pro e la variante www non risolvono dalMac in due prove. Rimosso il link dalle note legali, mantenendo tanggo.software HTTP200/TLSconforme; anche CV, Garante, FAQcookie e diritti HTTP200. Nessuna modifica DNS. R17 su98a4959f:196PHP/2218asserzioni e20browserPASS, unica differenza applicativa daR16 nel link. Kit5ff5bba3 depositato ma mai avviato in PREPARE/S0/RUN, conservato inattivo. Nuova pubblicazione da eseguire sul successivo commit integrato.
