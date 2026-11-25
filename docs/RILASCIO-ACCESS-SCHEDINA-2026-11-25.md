# Rilascio Access → Bozze Schedina — 25 novembre 2026

**PUBBLICATO E VERIFICATO NEL PERIMETRO; BASELINE IN AUDIT generale invariata.** Autorizzazione esplicita del titolare a GitHub/main, online, migrazione e due sole strutture.

- Schedina: PR61 integrata, applicativo `fdd4d9b0ca3a3f8047721cf2c8c0db0a1a608eac`.
- Access: repository privato indipendente `joomlu/tanggo-access`, revisione `dc879e66ddbdb6f1b76abb38b2339106a2e35549`, release `20261125-schedina-api-v11`.
- Destinazioni attive: Hotel Tanggo Igea Marina94 e Hotel Tanggo Bellaria95, proprietario61 e reception156/157. Rimini96 priva di collegamento, zero importazioni.
- Migrazione additiva `access_importazioni` eseguita, 74→75 tabelle; configurazioni riservate distinte e credenziali separate. APP_KEY e dati preesistenti conservati.

## Protezione e recupero

Cinque interruzioni sintetiche nella VM ufficiale positive sul recupero prima della mutazione, Git, ambiente, cache e nuova tabella. Checkpoint SQL fresco cifrato con chiave separata e ripristino nativo su MariaDB senza rete, controllo delle 74 tabelle prima della migrazione; codice Git ripristinato in copia separata, cache/ambienti e tre database Access custoditi. Guardiano systemd e lock del rilascio.

Prima attivazione NON positiva: il file privato era sotto una directory /etc non attraversabile dal servizio Access. Recupero automatico riuscito: tabella nuova ancora vuota rimossa, codice/cache/configurazioni precedenti ripristinati e servizi riaperti. Nessun dato reale perso o permesso allargato. Corretto il percorso nel deposito privato già accessibile al servizio, con file0600; seconda finestra con nuovo checkpoint e recupero nativo positivo. Rilascio R2 accettato in12,555s di runtime del servizio (non durata separata del disservizio). Nessuna protezione SELinux disattivata: il comando diagnostico id-Z ha segnalato assenza di SELinux sul destinatario; VM ufficiale e server sono ambienti distinti.

## Collaudo online

Login Access, due destinazioni esatte e rifiuto Rimini; due inviti fittizi, ciascuno con documento del titolare, adulto e bambino. Conferma ospite, salvataggio documento, approvazione dati/componenti, abilitazione e invio selfie disegnata, confronto approvato per la sola fixture, arrivo e conferma check-in. POST HTTPS reale Access→Schedina riuscito. Reinoltro dalla UI/API Access e ripetizione dello stesso payload al ricevente: stessi ID, nessun duplicato.

| Hotel | Check-in fittizio | Bozza Schedina | Persone |
|---|---|---:|---:|
| Igea Marina | CI-00000013 | 539 | titolare +2componenti |
| Bellaria | CI-00000014 | 540 | titolare +2componenti |

Lettura DB indipendente: circuito bozza, struttura corretta, autorità di rilascio e data di nascita conservate, 3persone/1camera/3letti, consenso privacy falso e nessun invio Questura/ISTAT. Payload della coda cancellati dopo ricevuta. Chrome: ricevute delle due strutture visibili in Access, bozza Bellaria e due componenti aperti in Schedina. Primo accesso con Rimini già selezionata restituisce404 per isolamento previsto; selezionando Bellaria il collegamento si apre. Manuale esplicito sul cambio struttura. Nessun salvataggio definitivo o invio enti nel browser.

Gli inviti fittizi Access sono chiusi con check-out e i documenti/storici di prova nel cestino recuperabile. Le due bozze dimostrative sono conservate e riconoscibili dalle osservazioni COLLAUDO API V11; nessuna schedina reale modificata. Le tre schede reali restano nel servizio Access. Nessun messaggio/email/WhatsApp spedito a terzi.

## Uso e limiti

[Manuale rapido](MANUALE-ACCESS-SCHEDINA.md). Access e Schedina rimangono indipendenti. I dati mancanti, i consensi, la nascita testuale del titolare, i cataloghi/classificazioni e le esenzioni si controllano in Schedina prima della conferma. Nessuna sincronizzazione automatica delle modifiche successive; nessun documento reale importato retroattivamente. PIN ancora dimostrativo, nessuna serratura reale.

83test Node24 e61test PHP/913asserzioni della qualifica precedente restano validi per lo stesso codice; 23guardie di override/build/GEO positivi. Nessuna ripetizione indiscriminata della suite dopo sole modifiche documentali. Le evidenze private sono in `/private/tmp/access-schedina-20261125`; segreti e database esclusi da GitHub. Repository Access pubblicato da archivio selettivo, senza cartelle private o storia locale non verificata.

CODICE MODIFICATO SI · TEST MODIFICATI SI · DOCUMENTAZIONE MODIFICATA SI · CONFIGURAZIONE MODIFICATA SI · SCHEMA MODIFICATO SI (migrazione approvata) · DATI DI COLLAUDO SI (fittizi) · TEST ESEGUITI SI · COMMIT SI · PUSH SI · DEPLOY SI · TRASMISSIONI ENTI NO · TRASMISSIONE ACCESS→SCHEDINA SI (sole fixture online).
