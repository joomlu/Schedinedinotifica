# Scheda Cliente Module

Modulo encapsulato per la gestione della scheda cliente (`/clienti/nuovo`) con dipendenze dichiarate.

## Ambito

- Nuovo cliente (wizard completo)
- Modifica cliente
- Lista clienti con stato tipo cliente
- Consultazione del catalogo di sistema `Tipo Cliente` (OSPITE, COMPONENTE, RICHIESTA), immutabile anche per Superadmin.

La fonte documentale maestra è il [Maestro ufficiale](maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md). La [verifica del wizard del 15 ottobre 2026](VERIFICA-WIZARD-CLIENTE-2026-10-15.md) distingue navigazione Anagrafica, salvataggio a bozza, salvataggio cliente e apertura della nuova schedina; contiene matrice dei minimi ed evidenze, senza autorizzare invii agli enti.

## Dipendenze principali

- Tabelle: vedi `config/scheda_cliente.php` (`tables`)
- Route: vedi `config/scheda_cliente.php` (`routes`)
- File modulo: vedi `config/scheda_cliente.php` (`files`)

## Export

Comando:

```bash
php artisan scheda-cliente:export
```

Output:

- ZIP in `storage/app/exports/`
- Manifest JSON con:
  - lista file inclusi
  - file mancanti
  - tabelle e route richieste

Target personalizzato:

```bash
php artisan scheda-cliente:export --target=/percorso/assoluto/export
```

