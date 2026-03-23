# Scheda Cliente Module

Modulo encapsulato per la gestione della scheda cliente (`/clienti/nuovo`) con dipendenze dichiarate.

## Ambito

- Nuovo cliente (wizard completo)
- Modifica cliente
- Lista clienti con stato tipo cliente
- Configurazione `Tipo Cliente` (CRUD)

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

