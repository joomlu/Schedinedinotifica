# Capienza Presenze — analisi in lettura, 28 ottobre 2026

L’ampliamento a 64 GiB è sospeso su richiesta dell’utente. Nessuna cancellazione, deduplicazione, nuova copia integrale, variazione di risorse, test Laravel o operazione produttiva eseguita in questa fase. Sono stati creati soltanto rapporti e metadati di misura. Il limite di recupero resta 240 secondi. La proposta precedente di ampliamento era prematura rispetto a questo inventario.

## Capacità virtuale e occupazione esterna

GiB = 1.073.741.824 byte. Misure su sistemi attivi, senza congelare database o volumi.

| Oggetto | Capacità virtuale | Occupazione misurata |
|---|---:|---:|
| Disco virtuale VM | 56,000 GiB | 54,393 GiB di blocchi contabilizzati per il file disco |
| Filesystem Linux utilizzabile | 54,729 GiB | 51,504 GiB usati; **3,226 GiB disponibili** |
| Contenitore cifrato sparsebundle su Extra | 128,000 GiB | **92,419 GiB di blocchi contabilizzati**, ultima lettura |
| Intero disco esterno APFS | 931,191 GiB fisici | 578,770 GiB usati e 352,421 GiB liberi, su due volumi |

Il contenitore contiene anche archivi Git e due immagini di recupero precedenti da 48 GiB virtuali ciascuna. La prima lettura del sparsebundle era 90,616 GiB allocati, l’ultima 92,419 GiB: il sistema e i volumi erano attivi. Non viene attribuita la variazione a una causa non misurata. Nessuna nuova immagine o copia integrale è stata creata dall’analisi.

Le dimensioni dei singoli file non rappresentano automaticamente blocchi esclusivi: APFS e XFS usano copie CoW. Il totale nominale `du` del contenuto Schedine nel contenitore era circa 219,351 GiB, superiore ai blocchi del contenitore; non è spazio recuperabile per intero. Anche `/home` nella VM conta nominalmente 217,602 GiB su un filesystem di 54,729 GiB. FIEMAP ha confermato un blocco fisico identico e condiviso fra il progetto originale e un clone. Liberare spazio dentro Linux non garantisce la stessa riduzione immediata del file sparsebundle su Extra; nessun trim è stato eseguito.

## Ripartizione nella VM

I valori `du` sono allocazioni contabilizzate per percorso e **non sono sommabili come spazio fisico esclusivo**. Dove indicato, FIEMAP misura invece l’unione degli intervalli dati o i blocchi esclusivi.

| Categoria | Dimensione | Interpretazione |
|---|---:|---|
| Progetto originale `/home/collaudo.guest/schedine-sviluppo` | 2,013 GiB `du` | Da preservare; include 1,259 GiB Git e 0,386 GiB vendor/node_modules |
| Copie del progetto | 124 directory Laravel incluso l’originale | Molte condividono blocchi; nessun risparmio promesso sommando le directory |
| Database attivo MariaDB 11.8.9 | 0,166 GiB `du` | `/home/collaudo.guest/.local/share/schedine-mariadb-11.8.9/data`, da preservare |
| Backup locali `/var/lib/schedine-backup` | 0,342 GiB `du` | Da preservare |
| Nove custodie storiche nella home | 0,311 GiB `du` | Da preservare; nessuna equivalenza o superfluità presunta |
| Ingressi cifrati C5 | 14,790 GiB esclusivi | 163 directory, 206 file cifrati presenti; nessun duplicato di cifrato rilevato dai sigilli disponibili |
| SQL decodificati dei recuperi | 1,562 GiB di dati fisici referenziati | 145 file, 3,137 GiB nominali; una parte è già condivisa, da preservare |
| Inventari decodificati | 0,958 GiB esclusivi | 167 file, necessari alla tracciabilità |
| Archivi FS decodificati rimasti | 0,950 GiB esclusivi | 40 file; rigenerabili solo dopo controllo della sorgente cifrata e della chiave, non inclusi nel risparmio proposto |
| Estrazioni complete C5 rimaste | 45,015 GiB nominali `du` | 87 estrazioni, blocchi condivisi: risparmio esclusivo complessivo non misurato e non promesso |
| Datadir MariaDB temporanei C5 | **2,321 GiB esclusivi** | Dodici datadir non vuoti e due vuoti; nessuno attivo; candidati alla proposta sotto |
| Log `/var/log` | 0,307 GiB `du` | Circa 0,256 GiB journal; non proposti per cancellazione |
| Cache `/var/cache` | 0,089 GiB `du` | Prevalentemente pacchetti; guadagno marginale, nessuna azione |
| `Library/Caches` della home | 0,318 GiB `du` | Contenuto non classificato come eliminabile dal solo nome |
| `/var/tmp` | 0,518 GiB `du` | Materiali storici da classificare, esclusi dal recupero proposto |
| Runtime `/opt` | 0,439 GiB `du` | Necessario agli strumenti, da preservare |

L’inventario conta 168 radici di recupero C5, anche parziali o già ridotte. La scansione `du` di `/tmp` ha segnalato soltanto una directory effimera di systemd scomparsa durante la misura; non un errore sui dodici datadir candidati. Non è stata calcolata un’attribuzione esclusiva completa fra tutti i cloni: il piano non dipende da tale stima.

## Copie necessarie, duplicate e rigenerabili

Conservare progetto originale, modifiche locali, database attivo, backup locali e **tutti i cifrati PREPARE/S0, SQL, ridump, inventari, chiavi, ricevute e diagnostici**. Cifrati di checkpoint diversi non sono duplicati solo perché contengono database simili. I sigilli disponibili non mostrano cifrati identici fra gli ingressi presenti.

I dodici datadir sono ricostruzioni operative dei recuperi, non le uniche copie dei dati. Per ciascuno sono stati verificati: SQL e ridump identici, SQL corrispondente al sigillo, cifrato DB presente con SHA256 corretto, chiave presente e archivio FS cifrato presente. Nessun riferimento aperto ai datadir rilevato. I grandi cifrati FS non sono stati tutti ricalcolati e non è stato effettuato un nuovo recupero integrale. Due datadir derivano dallo stesso checkpoint: duplicazione funzionale, senza affermare che ogni file binario del datadir sia identico.

Copie di lavoro e dipendenze sono in parte rigenerabili, ma molte sono già CoW e possono contenere differenze locali: nessun ritiro o deduplicazione indiscriminata proposto. Le due immagini VM storiche restano conservate; uguale dimensione non dimostra identità dei contenuti.

## Quanto spazio serve a C5

Nel RUN precedente erano liberi 3.532.804.096 byte; il minimo campionato fu 941.301.760. Il consumo aggiuntivo osservato è quindi **2.591.502.336 byte = 2,414 GiB**. Aggiungendo riserva invariata di 1 GiB e margine invariato di 32 MiB, il minimo ricostruibile è **3,445 GiB liberi**: deficit attuale circa **0,219 GiB**, non 8 GiB.

Se occorre un nuovo PREPARE residente, le misure precedenti aggiungono 672.428.032 byte di ingresso cifrato e 53.678.080 byte rimasti dopo il ritiro dei derivati: altri **0,676 GiB**, portando il minimo a **4,121 GiB liberi**, con deficit attuale circa **0,895 GiB**. La misura è dello stesso circuito e va aggiornata se cresce il contenuto.

Questi sono **limiti inferiori**, non un picco finale qualificato: il RUN è stato interrotto. Servono contemporaneamente base decifrata, archivio combinato completo, estrazione completa e temporanei di verifica; il solo delta trasferito non misura il fabbisogno di recupero. Dopo i confronti integrali alcuni intermedi sono rigenerabili, ma i controlli non possono essere omessi. Il timeout di 240 secondi è distinto dalla capienza; non è dimostrato che un disco più grande lo risolva.

## Proposta entro i 56 GiB — non eseguita

1. Mantenere invariati originali, dati locali, tutte le custodie e i materiali di recupero elencati.
2. In una fase successiva autorizzata, ricontrollare assenza di utilizzi e catena dei dodici datadir e ritirare **soltanto quei datadir temporanei**. Blocchi esclusivi misurati: **2.492.194.816 byte = 2,321 GiB**, senza conteggiare ulteriori risparmi da metadati.
3. Lo spazio libero teorico passerebbe da circa **3,226 a 5,547 GiB**. È un margine concreto per una verifica controllata del fabbisogno senza ampliare la VM, non una ricevuta C5 già positiva.
4. Tenere una sola materializzazione di recupero attiva alla volta. Prima di un altro RUN produttivo, qualificare picco completo e durata entro 240 secondi in una fase distinta autorizzata, preservando guardie, riserva e checkpoint freschi. Nessuna prova integrale è stata avviata durante questa analisi.

Percorsi dei soli candidati:

- `/tmp/schedine-linux-cold-final-uo5sn2pb/dbdata`
- `/tmp/schedine-linux-cold-final-qhwp6fmf/dbdata`
- `/tmp/schedine-linux-cold-final-un2mr8zn/dbdata`
- `/tmp/schedine-linux-cold-final-0zeo_039/dbdata`
- `/tmp/schedine-linux-cold-final-rn47fuv5/dbdata`
- `/tmp/schedine-linux-cold-final-qcxghlon/dbdata`
- `/tmp/schedine-linux-cold-final-np3j2g7i/dbdata`
- `/tmp/schedine-linux-cold-final-4otw_mrt/dbdata`
- `/tmp/schedine-linux-cold-final-o4w8a0go/dbdata`
- `/tmp/schedine-linux-cold-final-0hfjkc04/dbdata`
- `/tmp/schedine-linux-cold-final-qsnunu77/dbdata`
- `/tmp/schedine-linux-cold-final-z0xz3kwh/dbdata`

Nessuno dei percorsi è stato rimosso. Produzione non contattata né modificata in questa analisi. Nessun commit, push, deploy o cambiamento applicativo. [Misure e criteri](evidenze/capienza-presenze-2026-10-28/misure.json).

## Esecuzione autorizzata del 29 ottobre 2026

La fase di sola lettura sopra è storica. L’utente ha poi autorizzato esclusivamente i dodici datadir elencati, previa nuova verifica. Tutti dodici sono stati verificati nuovamente e ritirati; fonti SQL/ridump/cifrati/chiavi/sigilli e diagnostici conservati. Liberi3.463.286.784→5.956.538.368byte, recupero reale2.493.251.584byte. VM56GiB invariata, nessuna nuova copia integraleVM.

Recupero completo profilato170,700sPASS; nuovo C5 della PR46PASS/S0161,731s/RUN265,062s e455318e7 online, riserva1GiB rispettata. Nuova QA estesa rileva però il residuoURL su ricarica del filtro condiviso: correzione ulteriore locale, nuovo collaudo e gate distinti dal rilascioPR46. [Consuntivo corrente](RISOLUZIONE-C5-PRESENZE-2026-10-29.md). Nessun’altra pulizia autorizzata o eseguita sui materiali preesistenti.
