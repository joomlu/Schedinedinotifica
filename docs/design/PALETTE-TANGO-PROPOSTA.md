# Paletas Visuales - Stato Attuale e Proposta Tango

Data: 2026-03-17
Progetto: Schedinedinotifica / Tanggo
Scopo: congelare la paleta attuale come `Paleta 1` e definire una `Paleta 2` pronta da applicare in un secondo momento, senza toccare ora il codice del tema attivo.

## Paleta 1 - Hoteleria Elegante (attuale)
Questa e la paleta oggi attiva nel progetto.

### Brand e stati
- Primary / Brand: `#1F6F78`
- Success: `#2E8B57`
- Warning: `#D9A441`
- Danger: `#C65A46`
- Blue base: `#32CCFF`
- Teal: `#02A8B5`
- Cyan / supporto: `#1F6F78`

### Fondi e neutrali
- Body background: `#FFFFFF`
- Sidebar light: `#F6F7F5`
- Sidebar dark: `#16313A`
- Topbar dark: `#21424C`
- Topbar dark item: `#2C5561`
- Footer light: `#F6F7F5`
- Border soft: `#E9EBEC`
- Gray 100: `#F3F6F9`
- Gray 200: `#EFF2F7`
- Gray 600: `#878A99`
- Gray 700: `#495057`
- Gray 900: `#212529`

### Lettura della identita attuale
- elegante e sobria
- pulita e amministrativa
- caldo-fredda equilibrata
- adatta a contesto hotel + gestionale
- molto coerente per un software professionale

## Paleta 2 - Tango Hotel Elegante Lusso (proposta)
Obiettivo: evolvere l'identita da “hotel elegante sobrio” a “tango argentino premium”, mantenendo leggibilita alta, contrasto corretto e uso del rosso solo nei punti di marca e azione principale.

### Brand e stati
- Primary / Brand: `#8C1D2C`
- Primary Hover: `#741826`
- Primary Soft: `#E8D2D6`
- Success: `#2F7A57`
- Success Soft: `#DCEEE4`
- Info: `#4E748C`
- Info Soft: `#DCE8EF`
- Warning: `#B68A3A`
- Warning Soft: `#F3E8CF`
- Danger: `#B43A3A`
- Danger Soft: `#F1D9D9`

### Struttura e superfici
- Sidebar Dark: `#1A1A1D`
- Sidebar Hover: `#26262A`
- Topbar Dark: `#232328`
- Body Background: `#F7F3EE`
- Card Background: `#FFFFFF`
- Border Soft: `#E6DED4`
- Accent Gold: `#C6A15B`
- Accent Ivory: `#FBF8F3`

### Testi
- Heading / Main Text: `#2B2B2B`
- Secondary Text: `#6B6761`
- Muted Text: `#8C867F`
- Text on Dark: `#F5F1EB`
- Text on Primary: `#FFFFFF`
- Link: `#8C1D2C`
- Link Hover: `#741826`

## Intenzione estetica della Paleta 2
La proposta non vuole trasformare il software in qualcosa di teatrale o pesante. L'idea e:
- tango argentino come ispirazione, non come costume
- hotel premium, non locale notturno
- rosso profondo come firma di marca
- nero morbido per struttura e profondita
- avorio caldo per dare luce e calore
- oro elegante per warning, dettagli premium e accenti raffinati

## Regole d'uso della Paleta 2

### 1. Rosso solo nei punti chiave
Usare il rosso tango `#8C1D2C` per:
- azioni primarie
- item attivi
- badge di marca
- link importanti
- focus visivo

Non usarlo come sfondo dominante di tutte le cards o tabelle.

### 2. Nero per struttura, non per aggressione
Usare i neri profondi per:
- sidebar
- topbar dark
- contesti di navigazione
- header secondari

Evitare nero pieno assoluto ovunque.

### 3. Avorio per respirazione visiva
Usare `#F7F3EE` e `#FBF8F3` per:
- body
- aree secondarie
- superfici morbide
- contesto di formulari e cards

### 4. Oro solo come dettaglio premium
Usare `#B68A3A` / `#C6A15B` per:
- warning eleganti
- dettagli select premium
- piccoli highlight
- tabs o badge speciali

Non convertirlo in giallo brillante.

## Mappatura proposta a Bootstrap / Velzon

### Bootstrap semantic mapping
- `--bs-primary`: `#8C1D2C`
- `--bs-primary-rgb`: `140, 29, 44`
- `--bs-success`: `#2F7A57`
- `--bs-info`: `#4E748C`
- `--bs-warning`: `#B68A3A`
- `--bs-danger`: `#B43A3A`
- `--bs-body-bg`: `#F7F3EE`
- `--bs-body-color`: `#2B2B2B`
- `--bs-border-color`: `#E6DED4`
- `--bs-secondary-color`: `#6B6761`
- `--bs-tertiary-color`: `#8C867F`

### Velzon structural mapping
- Vertical menu bg dark: `#1A1A1D`
- Vertical menu hover: `#26262A`
- Vertical menu active bg: `rgba(140, 29, 44, .18)`
- Vertical menu active color: `#F5F1EB`
- Header dark bg: `#232328`
- Header dark item bg: `rgba(255,255,255,.06)`
- Footer light bg: `#FBF8F3`
- Card bg: `#FFFFFF`
- Soft blocks: `#FBF8F3`

## Applicazione prevista per area

### Sidebar
- fondo nero elegante
- testo chiaro caldo
- attivo in rosso tango
- hover leggermente piu chiaro, non luminoso

### Topbar
- dark raffinata
- icone e testo caldi chiari
- dropdown puliti su fondo bianco o avorio

### Bottoni
- primary: rosso tango
- primary hover: rosso piu profondo
- soft primary: fondo rosato `Primary Soft`
- warning: oro elegante, non giallo duro
- success: verde profondo e sobrio

### Cards
- fondo bianco
- bordo morbido avorio/grigio caldo
- ombra leggera, mai eccessiva
- header su `Accent Ivory` o `Body Background`

### Formulari
- input su fondo chiaro pulito
- focus ring rosso molto leggero
- label testo scuro
- helper text muted caldo

### Tabelle
- zebra lieve su avorio
- hover con tinta calda chiarissima
- header sobrio
- badges coerenti con i colori semantic

### Tabs e navigazione secondaria
- tab attiva rosso tango o underline rosso
- tabs passive neutre calde
- niente saturazione eccessiva

### Alerts
- success soft verde
- info soft blu-grigio elegante
- warning soft oro avorio
- danger soft rosso polveroso

## Contrasto e accessibilita
- testo scuro su fondo chiaro: sempre almeno medio-alto contrasto
- testo chiaro su rosso e nero: usare `#FFFFFF` o `#F5F1EB`
- warning su oro: usare testo scuro, non bianco
- link e bottoni devono restare ben leggibili anche su mobile

## Differenza strategica tra Paleta 1 e Paleta 2

### Paleta 1
- piu neutra
- piu gestionale classica
- piu “hotel elegante discreto”

### Paleta 2
- piu identitaria
- piu tangibile come brand Tanggo
- piu calda
- piu premium
- piu memorabile senza diventare teatrale

## Raccomandazione finale
Se l'obiettivo e chiudere il software con una identita piu forte e proprietaria, la `Paleta 2` e una proposta valida.

Se l'obiettivo immediato e minimizzare rischio visivo prima della chiusura dei moduli, mantenere `Paleta 1` attiva e applicare `Paleta 2` in un passaggio dedicato e controllato e la scelta piu sicura.

## Stato attuale
- Nessun cambio applicato al codice del tema attivo in questo passaggio.
- Questo documento serve come baseline + proposta pronta da implementare quando decidiamo il cambio globale.
