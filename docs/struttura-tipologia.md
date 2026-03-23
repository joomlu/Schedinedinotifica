# Componente Riutilizzabile: Struttura Tipologia

## Obiettivo
Componente a cascata con 3 livelli:

1. `Tipologia generale`
2. `Tipologia struttura`
3. `Classificazione`

La classificazione e valida solo se collegata alla tipologia struttura (pivot `classificazione_tipologia`).

## Componente Blade
Nuovo componente riutilizzabile:

- `x-xx.struttura-tipologia`
- file: `resources/views/components/xx/struttura-tipologia.blade.php`

Compatibilita mantenuta:

- `x-struttura.filtro-tipologie` ora delega al nuovo componente.

## Modello Dati
Tabelle coinvolte:

- `tipologie_generali`
- `tipologie_struttura` (FK `tipologia_generale_id`)
- `classificazioni`
- `classificazione_tipologia` (pivot tra classificazione e tipologia struttura)

Su `struttura` restano sia i riferimenti FK (`*_id`) sia i campi testuali legacy (`tipologia_generale`, `tipologia_struttura`, `classificazione`).

## Validazione Back-end
`StrutturaRequest` verifica:

1. che `tipologia_struttura_id` appartenga alla `tipologia_generale_id` scelta
2. che `classificazione_id` sia ammessa per la `tipologia_struttura_id` scelta (via pivot)

## Catalogo Dati (Export)
Catalogo pronto per riuso/export:

- `reference/componentes/struttura_tipologia_catalogo.json`

Seed applicativo:

- `database/seeders/TipologieSeeder.php`

## Fonti Online Usate per allineamento catalogo
Nota: in Italia la classificazione operativa varia per regione; il catalogo qui e una base coerente e estendibile.

- Istat, classificazione esercizi ricettivi (alberghieri / extra-alberghieri / all'aperto):  
  https://www.istat.it/it/files//2019/08/03_N_Esercizi-ricettivi.pdf
- Regione Emilia-Romagna, strutture ricettive alberghiere e classificazioni stelle/RTA:  
  https://imprese.regione.emilia-romagna.it/semplificazione-e-sportello-unico/schede-attivita-imprenditoriali/strutture-ricettive/strutture_ricettive_alberghiere_scheda_informativa.pdf/%40%40download/file/Strutture_ricettive_alberghiere_Scheda_informativa.pdf  
  https://imprese.regione.emilia-romagna.it/turismo/temi/alberghi
- Regione Lombardia, classificazione agriturismi con marchio Agriturismo Italia (girasoli):  
  https://www.regione.lombardia.it/wps/portal/istituzionale/HP/DettaglioRedazionale/servizi-e-informazioni/Imprese/Imprese-agricole/Agriturismo%252C%2BEnoturismo%2Be%2BOleoturismo/agriturismo-classificazione-in-lombardia/agriturismo-classificazione-in-lombardia  
  https://www.agriturismoitalia.gov.it/flex/AppData/Redational/Contents/accesso_aziende/istruzioni_operative_LOMBARDIA.pdf
