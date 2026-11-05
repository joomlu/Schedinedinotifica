# Questura — piano circoscritto di capacità C5

Situazione iniziale: il nuovo rilascio non poteva iniziare con circa 2,56 GB liberi nella VM durante il collaudo. Sono stati identificati e verificati in sola lettura tre recuperi ISTAT precedenti già conclusi. È stato eseguito il ritiro esclusivo delle sottocartelle `extracted`, `dbdata`, `db-tmp` dei seguenti recuperi, che contengono copie ricostruibili, senza intervenire su originali o backup:

| Recupero precedente | Directory nella VM | Record verificati | Byte allocati ricostruibili |
|---|---|---:|---:|
| PREPARE ISTAT | `/tmp/schedine-linux-cold-final-8yv5uu7a` | 23.960 | 1.190.117.376 |
| Primo S0, recupero completo ma non accettato dal server prima dello stop | `/tmp/schedine-linux-cold-final-_tl2klu9` | 23.976 | 1.190.182.912 |
| S0 finale accettato da C5 | `/tmp/schedine-linux-cold-final-01l2zlkt` | 23.987 | 1.190.240.256 |

Totale allocato: **3.570.540.544 byte**. La liberazione effettiva può essere inferiore in presenza di blocchi condivisi: è obbligatorio misurare nuovamente prima di PREPARE e RUN.

Tre preflight PASS distinti: cifrati originali e relativi hash, SQL decifrato e ridump identici, inventario e contenuti estratti conformi, metadati e label SELinux conformi, chiave separata privata invariata, nessun processo con file aperti nei target. I recuperi sono identificati univocamente dai sigilli originali, non dal solo nome della directory. Il primo S0 storico resta NON accettato dal server: non viene promosso a checkpoint del futuro rilascio.

Procedura: ripetere integralmente i controlli prima di ogni ritiro, salvare il manifest dei soli derivati con hash e metadati nella directory del recupero, rimuovere esclusivamente le tre sottocartelle nominate, ricontrollare cifrati/SQL/chiave e registrarne l’esito. Restano conservati `decoded`, SQL, cifrati, chiavi, sigilli, inventari, log, ricevute e le custodie Mac/VM/Extra. Nessun backup o dato quotidiano viene eliminato. Nessuna crescita ulteriore del disco o interruzione della VM prevista.

Dopo il ritiro: nuova misura reale della capacità e nuove stime conservative, riserve VM 1 GiB / Mac 8 GiB / Extra 40 GiB invariate. Solo se conformi potranno iniziare un nuovo PREPARE, un nuovo S0 indipendente e il rilascio C5 di Questura; nessun riuso di S0 precedente e nessun allentamento dei budget o dei controlli. Se lo spazio effettivo non basta, fermarsi prima di PREPARE/RUN e documentare il blocco.

**Consuntivo: tre preflight e tre ritiri circoscritti PASS.** Dopo ogni ritiro cifrati, SQL e chiave ricontrollati; manifest e ricevuta conservati. Ultimo campione durante il collaudo: 6.142.074.880 byte liberi. Nessun originale o backup rimosso. Restano obbligatori nuove misure prima di PREPARE e RUN.

## Nuovo PREPARE Questura e seconda finestra C5

Dopo PREPARE completo PASS e custodia cifrata verificata Mac/VM/Extra, la prima finestra RUN è stata fermata prima di execute/DDL per ricevuta non giunta entro il margine, in presenza di sospensioni Mac. S0 storico custodito, baseline riaperta sana, guardiano concluso. Nessun checkpoint del primo RUN riutilizzato.

Un quarto ritiro riguarda esclusivamente `extracted`, `dbdata`, `db-tmp` del nuovo recupero PREPARE Questura già verificato: **24.364 record / 1.213.341.696 byte allocati**. Preflight e ripetizione integrale prima del ritiro PASS, manifest e ricevuta conservati; cifrati originali, SQL e ridump, inventario, sigilli, chiave, log e custodie preservati. Il nuovo S0 ricostruisce dalla base cifrata conservata, senza dipendere dai derivati ritirati.

Prima di RUN2: **4.495.036.416 byte liberi** contro fabbisogno prudenziale **3.461.763.072**, incluse crescita storica, 32 MiB e riserva VM 1 GiB. Mac 8 GiB / Extra 40 GiB e budget 1800/600/1379/421, trasferimento 120 / ricevente 240 invariati. Nuovi 55 campioni di quiete PASS. Secondo S0 fresco in 150,140 s e C5 PASS in 260,968 s, zero DDL. Dati quotidiani e 84 file protetti invariati, VM mantenuta attiva. Nessuna espansione o rimozione di backup.
