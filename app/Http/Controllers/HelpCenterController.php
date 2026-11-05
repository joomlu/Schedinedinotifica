<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpCenterController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if (!$user?->isSuperAdmin() && !$user?->isAdmin()) {
            return redirect()->route('help.general');
        }

        return view('help.index');
    }

    public function general(): View
    {
        ['guide' => $guide, 'modules' => $modules, 'faqs' => $faqs, 'personas' => $personas, 'troubleshooting' => $troubleshooting, 'managementTopics' => $managementTopics] = $this->buildHelpData();
        $guide = $this->orderGuide($guide);
        $modules = $this->orderModules($modules);
        $personas = $this->orderPersonas($personas);
        $managementTopics = $this->orderManagementTopics($managementTopics);

        return view('help.general', [
            'guide' => $guide,
            'modules' => $modules,
            'faqs' => $faqs,
            'personas' => $personas,
            'troubleshooting' => $troubleshooting,
            'managementTopics' => $managementTopics,
        ]);
    }

    public function admin(): View
    {
        $user = auth()->user();
        abort_unless($user?->isSuperAdmin() || $user?->isAdmin(), 403);

        ['adminTopics' => $adminTopics] = $this->buildHelpData();
        $adminTopics = $this->orderAdminTopics($adminTopics);

        return view('help.admin', [
            'adminTopics' => $adminTopics,
        ]);
    }

    public function print(Request $request): View
    {
        ['guide' => $guide, 'modules' => $modules, 'faqs' => $faqs, 'personas' => $personas, 'troubleshooting' => $troubleshooting, 'managementTopics' => $managementTopics, 'adminTopics' => $adminTopics] = $this->buildHelpData();
        $guide = $this->orderGuide($guide);
        $modules = $this->orderModules($modules);
        $personas = $this->orderPersonas($personas);
        $managementTopics = $this->orderManagementTopics($managementTopics);
        $adminTopics = $this->orderAdminTopics($adminTopics);
        $section = trim((string) $request->query('section', ''));
        $module = trim((string) $request->query('module', ''));
        $topic = trim((string) $request->query('topic', ''));

        if ($module !== '') {
            $modules = array_values(array_filter($modules, fn (array $item) => ($item['slug'] ?? '') === $module));
        }

        if ($topic !== '') {
            $managementTopics = array_values(array_filter($managementTopics, fn (array $item) => ($item['slug'] ?? '') === $topic));
            $adminTopics = array_values(array_filter($adminTopics, fn (array $item) => ($item['slug'] ?? '') === $topic));
        }

        if ($section !== '') {
            if ($section === 'general') {
                $adminTopics = [];
            } elseif ($section === 'admin-index') {
                $guide = [];
                $personas = [];
                $modules = [];
                $faqs = [];
                $troubleshooting = [];
                $managementTopics = [];
            } elseif ($section === 'guide') {
                $personas = [];
                $modules = [];
                $faqs = [];
                $troubleshooting = [];
                $managementTopics = [];
                $adminTopics = [];
            } elseif ($section === 'personas') {
                $guide = [];
                $modules = [];
                $faqs = [];
                $troubleshooting = [];
                $personas = $topic === '' ? $personas : [];
                $adminTopics = [];
            } elseif ($section === 'modules') {
                $guide = [];
                $personas = [];
                $faqs = [];
                $troubleshooting = [];
                $managementTopics = [];
                $adminTopics = [];
            } elseif ($section === 'faqs') {
                $guide = [];
                $personas = [];
                $modules = [];
                $troubleshooting = [];
                $managementTopics = [];
                $adminTopics = [];
            } elseif ($section === 'troubleshooting') {
                $guide = [];
                $personas = [];
                $modules = [];
                $faqs = [];
                $managementTopics = [];
                $adminTopics = [];
            } elseif ($section === 'admin') {
                $guide = [];
                $personas = [];
                $modules = [];
                $faqs = [];
                $troubleshooting = [];
                $managementTopics = [];
            } else {
                $guide = [];
                $personas = [];
                $modules = [];
                $faqs = [];
                $troubleshooting = [];
                $managementTopics = [];
                $adminTopics = [];
            }
        }

        return view('help.print', [
            'guide' => $guide,
            'modules' => $modules,
            'faqs' => $faqs,
            'personas' => $personas,
            'troubleshooting' => $troubleshooting,
            'managementTopics' => $managementTopics,
            'adminTopics' => $adminTopics,
            'section' => $section,
            'module' => $module,
            'topic' => $topic,
        ]);
    }

    public function module(string $slug): View
    {
        ['modules' => $modules] = $this->buildHelpData();
        $modules = $this->orderModules($modules);
        $module = collect($modules)->firstWhere('slug', $slug);

        abort_unless($module, 404);

        return view('help.module', [
            'module' => $module,
            'modules' => $modules,
        ]);
    }

    public function management(string $slug): View
    {
        ['managementTopics' => $managementTopics, 'adminTopics' => $adminTopics] = $this->buildHelpData();
        $managementTopics = $this->orderManagementTopics($managementTopics);
        $adminTopics = $this->orderAdminTopics($adminTopics);
        $allTopics = array_merge($adminTopics, $managementTopics);
        $topic = collect($allTopics)->firstWhere('slug', $slug);
        $isAdminTopic = collect($adminTopics)->contains(fn (array $item) => ($item['slug'] ?? '') === $slug);
        $topicList = $isAdminTopic ? $adminTopics : $managementTopics;

        abort_unless($topic, 404);

        return view('help.management', [
            'topic' => $topic,
            'managementTopics' => $topicList,
            'isAdminTopic' => $isAdminTopic,
        ]);
    }

    private function buildHelpData(): array
    {
        $guide = [
            [
                'title' => 'Come si entra nel programma',
                'icon' => 'ri-login-circle-line',
                'keywords' => 'login accesso password proprietario reception utente',
                'summary' => 'Ogni struttura usa un nome di accesso comune. La persona che entra viene riconosciuta dalla sua password personale.',
                'result' => 'Alla fine dell accesso, in alto nel sistema si vede subito chi sta lavorando e con quale ruolo.',
                'steps' => [
                    'Inserisci il nome di accesso della struttura.',
                    'Inserisci la password personale della persona che sta entrando.',
                    'Se sei proprietario puoi creare nuovi utenti in Utenti e consegne.',
                ],
            ],
            [
                'title' => 'Come si configura la struttura',
                'icon' => 'ri-hotel-line',
                'keywords' => 'struttura configurazioni tipologia geo camere letti credenziali',
                'summary' => 'Prima del lavoro quotidiano conviene configurare bene struttura e configurazioni, cosi i moduli principali ereditano dati coerenti.',
                'result' => 'Il programma lavora in modo piu stabile e ogni select o classificazione parte gia corretta.',
                'steps' => [
                    'Apri Dati struttura e completa identita, apertura, camere, letti e GEO.',
                    'Controlla le Configurazioni principali, soprattutto gruppi, titoli, tipi documento e tipo alloggiato.',
                    'Inserisci le credenziali telematiche solo quando vuoi usare i collegamenti automatici.',
                ],
            ],
            [
                'title' => 'Come si lavora con clienti e schedine',
                'icon' => 'ri-send-plane-line',
                'keywords' => 'clienti schedine componenti arrivi web check in reception flusso base',
                'summary' => 'Il flusso operativo del programma parte quasi sempre da cliente e arriva alla schedina completa del soggiorno.',
                'result' => 'La pratica resta ordinata e pronta per statistiche, tassa di soggiorno, Questura e Tabella A Emilia-Romagna.',
                'steps' => [
                    'Crea o cerca prima il cliente.',
                    'Apri una nuova schedina, una bozza o un arrivo secondo il punto in cui si trova la pratica.',
                    'Se la pratica nasce dal cliente online, lasciala nel circuito Web Check-in fino a quando la reception la apre e la completa.',
                    'Completa ospite principale, componenti e dati di soggiorno prima di passare agli invii telematici.',
                ],
            ],
            [
                'title' => 'Come si gestiscono gli invii telematici',
                'icon' => 'ri-chat-1-line',
                'keywords' => 'questura tavola a xml txt invio diretto verifica ricevuta istat ross1000',
                'summary' => 'Gli invii telematici lavorano sempre con due strade: file ufficiale da scaricare oppure invio diretto se la struttura e pronta.',
                'result' => 'Hai sempre una soluzione praticabile, anche quando il canale automatico non e ancora disponibile o e in prova.',
                'steps' => [
                    'Apri Questura o Tabella A Emilia-Romagna e scegli il periodo corretto.',
                    'Controlla quali pratiche sono pronte e quali sono da correggere.',
                    'Scarica il file ufficiale oppure usa prima verifica invio diretto e poi invia direttamente.',
                ],
            ],
            [
                'title' => 'Come si chiede supporto',
                'icon' => 'ri-customer-service-2-line',
                'keywords' => 'supporto online ticket amministratore software assistenza struttura risposta',
                'summary' => 'Quando il manuale non basta, la struttura puo aprire un ticket di assistenza verso l amministratore del software.',
                'result' => 'Ogni problema resta tracciato per struttura, con priorita, stato e risposte fino alla chiusura del caso.',
                'steps' => [
                    'Apri Centro di supporto e vai in Supporto online.',
                    'Crea un nuovo ticket con titolo, categoria, priorita e descrizione completa del problema.',
                    'Segui le risposte dentro il ticket e chiudilo quando il problema e risolto.',
                ],
            ],
            [
                'title' => 'Come si chiude il turno',
                'icon' => 'ri-chat-1-line',
                'keywords' => 'consegne cambio turno messaggi reception apertura chiusura',
                'summary' => 'Il cambio turno si gestisce con le consegne: chi esce lascia le informazioni importanti e chi entra le legge e le continua se serve.',
                'result' => 'Il passaggio tra un turno e l altro non si perde e resta una traccia chiara di quello che e stato lasciato.',
                'steps' => [
                    'Apri Utenti e consegne.',
                    'Leggi prima le consegne aperte.',
                    'Lascia una nuova consegna per il turno successivo se serve.',
                ],
            ],
        ];

        $modules = [
            [
                'slug' => 'struttura',
                'title' => 'Struttura',
                'icon' => 'ri-building-line',
                'keywords' => 'struttura dati hotel camere letti apertura stagionalita istat questura logo credenziali',
                'route' => 'struttura.edit',
                'cta' => 'Apri struttura',
                'summary' => 'Configura i dati principali della struttura, le credenziali dei servizi telematici e la disponibilità di camere e letti.',
                'when' => 'Apri questa sezione quando devi impostare o correggere i dati generali dell hotel o della struttura, le credenziali dei servizi telematici e la disponibilita ufficiale di camere e letti.',
                'result' => 'Una struttura configurata bene permette di lavorare senza errori in schedine, Questura, Tabella A Emilia-Romagna, tassa di soggiorno e statistiche.',
                'items' => [
                    'Dashboard struttura con stato conto, licenza, scadenze e accessi rapidi',
                    'Dati anagrafici della struttura',
                    'Tipologia, classificazione e apertura',
                    'Camere disponibili e letti disponibili',
                    'Credenziali Questura e ISTAT / Tabella A Emilia-Romagna',
                ],
                'details' => [
                    'La Dashboard struttura e il punto di partenza: mostra licenza in uso, stato conto, scadenza principale, notifiche aperte e tutte le voci operative del menu in forma rapida.',
                    'Tipologia generale, tipologia struttura e classificazione definiscono come la struttura viene letta dal sistema e da alcuni flussi statistici. Vanno scelte in modo coerente con la reale categoria della struttura.',
                    'Nome struttura, ragione sociale, recapiti e logo servono sia alla gestione interna sia ai documenti stampati, per esempio ricevute e riepiloghi della tassa di soggiorno.',
                    'Apertura e stagionalita indicano se la struttura lavora tutto l anno o solo in un periodo. Le date di apertura e chiusura sono importanti per i controlli statistici e per Tabella A Emilia-Romagna.',
                    'Nel blocco GEO conviene usare prima CAP o comune, cosi il sistema completa gli altri dati. Se la struttura e estera o fuori Italia, usa il percorso estero senza cambiare il componente GEO.',
                    'Camere disponibili e letti disponibili sono la base per i report di occupazione e per Tabella A Emilia-Romagna. Vanno tenuti allineati con la disponibilita reale della struttura.',
                    'Le credenziali Questura e ISTAT / Tabella A Emilia-Romagna servono solo quando vuoi usare l invio diretto. Se non le hai ancora, il sistema continua a funzionare con il download manuale dei file ufficiali.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Dashboard e stato commerciale',
                        'items' => [
                            'Dashboard struttura: raccoglie tutte le voci operative del menu e mostra lo stato generale della struttura senza dover entrare in ogni modulo.',
                            'Licenza in uso: mostra il prodotto software attivo sulla struttura e il relativo tracking della licenza principale.',
                            'Scadenza principale: indica la prossima data critica, che puo essere la licenza o la scadenza del servizio struttura.',
                            'Stato conto: mostra in modo sintetico il rapporto commerciale con amministratore e superamministrazione.',
                        ],
                    ],
                    [
                        'title' => 'Identita della struttura',
                        'items' => [
                            'Tipologia generale: definisce la famiglia principale della struttura, per esempio alberghiera o altra tipologia prevista dal sistema.',
                            'Tipologia struttura: specifica il tipo reale della struttura all interno della tipologia generale, per esempio hotel, residence o altra voce coerente.',
                            'Classificazione: serve a indicare la categoria ufficiale della struttura e resta collegata al tipo scelto.',
                            'Nome struttura: e il nome commerciale che vedono operatori, clienti e stampe del sistema.',
                            'Ragione sociale: serve per i dati amministrativi e fiscali della struttura.',
                        ],
                    ],
                    [
                        'title' => 'Apertura e disponibilita',
                        'items' => [
                            'Tipo apertura: indica se la struttura lavora tutto l anno o solo in un periodo stagionale.',
                            'Data apertura e data chiusura: vanno usate quando la struttura non e annuale. Servono per controlli statistici e per Tabella A Emilia-Romagna.',
                            'Camere disponibili: indica quante camere la struttura puo realmente vendere o usare nel periodo di lavoro.',
                            'Letti disponibili: indica la disponibilita reale di letti della struttura e viene usata nei report di occupazione.',
                        ],
                    ],
                    [
                        'title' => 'Posizione e contatti',
                        'items' => [
                            'GEO struttura: usa prima CAP o comune quando possibile. Il sistema completa il resto in modo coerente.',
                            'Percorso estero: si usa solo quando la struttura non e in Italia. In quel caso si lavora con nazione, regione estero e citta estera.',
                            'Telefono, email, sito e logo citta: aiutano nella gestione, nei contatti e nelle stampe che il sistema produce.',
                        ],
                    ],
                    [
                        'title' => 'Credenziali telematiche',
                        'items' => [
                            'Questura username, password, WSKEY, codici e PUK: si compilano quando vuoi usare il canale diretto o preparare la struttura per l invio automatico.',
                            'ISTAT username, password e codice struttura Ross1000: servono per Tabella A Emilia-Romagna e per l eventuale invio diretto regionale.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'clienti',
                'title' => 'Clienti',
                'icon' => 'ri-team-line',
                'keywords' => 'clienti anagrafica residenza documento azienda bozza tipo cliente gruppo',
                'route' => 'customers',
                'cta' => 'Apri clienti',
                'summary' => 'Gestisce i clienti della struttura: ospiti, componenti e richieste.',
                'when' => 'Usa Clienti ogni volta che devi registrare una persona, aggiornarne i dati o preparare una scheda cliente completa da richiamare poi nelle schedine.',
                'result' => 'Una anagrafica cliente completa ti fa risparmiare tempo quando devi creare una nuova schedina o recuperare dati gia registrati.',
                'items' => [
                    'Nuovo cliente e modifica cliente',
                    'Anagrafica, residenza, documento e azienda',
                    'Salvataggio in bozza quando i dati non sono completi',
                    'Filtri per tipo cliente',
                ],
                'details' => [
                    'Il cliente e l anagrafica di base della persona. Conviene compilarlo bene una volta, cosi poi la schedina si prepara piu velocemente.',
                    'Tipo cliente distingue ospite, componente e richiesta. Questo aiuta a capire a colpo d occhio a che punto e la pratica e quali dati sono davvero necessari.',
                    'I blocchi anagrafica, residenza e documento seguono sempre la stessa logica del sistema: GEO coerente, calendari coerenti e select coerenti.',
                    'Se i dati non sono ancora completi, puoi salvare in bozza. Questo evita di perdere il lavoro quando una pratica viene completata in piu momenti.',
                    'La parte azienda si usa solo se il cliente ha davvero una azienda associata. In caso contrario resta disattivata per non sporcare i dati.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Dati base del cliente',
                        'items' => [
                            'Tipo cliente: distingue il ruolo della persona nel sistema, per esempio ospite, componente o richiesta.',
                            'Gruppi e sottogruppi: servono a classificare i clienti secondo l organizzazione scelta dalla struttura.',
                            'Titolo, nome, cognome e sesso: sono i dati minimi anagrafici da compilare con attenzione per evitare errori nei documenti e nelle schedine.',
                        ],
                    ],
                    [
                        'title' => 'Anagrafica e residenza',
                        'items' => [
                            'Geo anagrafica: indica luogo di nascita o provenienza anagrafica, secondo il blocco usato nel sistema.',
                            'Cittadinanza: si collega alla nazione scelta ma resta modificabile, perche puo non coincidere sempre con il paese di nascita.',
                            'Geo residenza: indica dove vive la persona. Non va confuso con l anagrafica.',
                            'Tipo via, strada, numero e CAP: servono a completare correttamente l indirizzo di residenza.',
                        ],
                    ],
                    [
                        'title' => 'Documento e dati aggiuntivi',
                        'items' => [
                            'Tipo documento, numero, rilascio e scadenza: diventano fondamentali quando il cliente deve essere usato in schedina o in invii ufficiali.',
                            'Rilasciato da: va scelto dalla tabella ufficiale del sistema per mantenere coerenza.',
                            'Azienda: si usa solo se il cliente ha un soggetto aziendale associato. In caso contrario la parte resta disattivata.',
                            'Bozza: permette di salvare il cliente anche se non tutti i dati sono ancora completi.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'schedine',
                'title' => 'Schedine',
                'icon' => 'ri-file-list-3-line',
                'keywords' => 'schedine arrivo partenza componenti presenze tassa soggiorno bozza arrivi web',
                'route' => 'schedina',
                'cta' => 'Apri schedine',
                'summary' => 'Raccoglie l ospite principale, i componenti, la permanenza e i dati utili per Questura, Tabella A Emilia-Romagna e tassa di soggiorno.',
                'when' => 'Usa Schedine quando il soggiorno entra nel circuito operativo ufficiale della struttura e devi registrare in modo completo ospite, componenti, permanenza e documento.',
                'result' => 'La schedina diventa il centro di lavoro per reception, Questura, Tabella A Emilia-Romagna, statistiche e tassa di soggiorno.',
                'items' => [
                    'Schedine ufficiali, bozze e arrivi',
                    'Controllo quantità persone e componenti',
                    'Dati ospite, componenti e documenti',
                    'Salvataggio continuo della schedina',
                ],
                'details' => [
                    'La schedina e il documento operativo principale del soggiorno. Contiene ospite principale, componenti, permanenza, documento, tassa di soggiorno e dati utili ai flussi telematici.',
                    'Quantita persone, quantita camere e quantita letti sono dati gestionali importanti. Servono per statistiche, occupazione e Tabella A Emilia-Romagna.',
                    'I componenti vanno caricati solo se esistono davvero. Se la schedina ha un solo ospite, non devi compilare campi di componenti.',
                    'Arrivo e partenza determinano presenze, arrivi, partenze e controlli di soggiorno. Vanno inseriti con attenzione per non falsare statistiche e report.',
                    'Il salvataggio continuo ti permette di lavorare a tappe. La pratica incompleta puo restare in bozza o in arrivi fino a quando non e pronta per diventare schedina ufficiale.',
                    'Web Check-in non entra piu direttamente in questo elenco: resta nel suo circuito dedicato finche la reception non apre la schedina web e decide come salvarla.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Dati iniziali della schedina',
                        'items' => [
                            'Numero schedina: viene assegnato dal sistema quando la pratica entra nel circuito corretto.',
                            'Arrivo e partenza: sono le date che governano presenze, partenze, occupazione, tassa di soggiorno e flussi telematici.',
                            'Tipo alloggiato ed esente: servono per classificare correttamente ospite principale e posizione rispetto alla tassa di soggiorno.',
                            'Quantita persone, quantita camere e quantita letti: descrivono il peso reale del soggiorno e alimentano occupazione e Tabella A Emilia-Romagna.',
                        ],
                    ],
                    [
                        'title' => 'Ospite principale e documento',
                        'items' => [
                            'Dati cliente: identificano la persona principale della schedina e vengono usati anche nei report e negli invii.',
                            'Geo anagrafica e geo residenza: vanno compilati in modo coerente e non vanno confusi tra loro.',
                            'Documento identita: tipo, numero, rilascio, scadenza e paese di rilascio sono indispensabili per Questura e per la corretta registrazione della persona.',
                        ],
                    ],
                    [
                        'title' => 'Componenti e altri blocchi',
                        'items' => [
                            'Componenti: si caricano uno alla volta solo quando esistono davvero altre persone oltre all ospite principale.',
                            'Tassa di soggiorno: usa i dati della schedina per calcolare importi, esenzioni e notti imponibili.',
                            'Dati statistici Tabella A Emilia-Romagna: restano collegati alla schedina ma non bloccano il lavoro se non disponibili.',
                            'Salva schedina: puoi salvare in piu momenti e completare la pratica senza perdere dati gia compilati.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'arrivi',
                'title' => 'Arrivi',
                'icon' => 'ri-calendar-check-line',
                'keywords' => 'arrivi prenotazioni importa in schedina arrivo nuovo arrivo',
                'route' => 'arrivals',
                'cta' => 'Apri arrivi',
                'summary' => 'Usa Arrivi per preparare le schedine non ancora entrate nel circuito ufficiale delle schedine.',
                'when' => 'Usa Arrivi quando vuoi preparare una pratica prima del check-in definitivo, senza trasformarla subito in schedina ufficiale.',
                'result' => 'Puoi lavorare in anticipo sulle pratiche e poi importarle nella schedina ufficiale al momento giusto.',
                'items' => [
                    'Creazione arrivo',
                    'Lista arrivi separata dalle schedine',
                    'Importazione in schedina ufficiale',
                ],
            ],
            [
                'slug' => 'web-checkin',
                'title' => 'Web Check-in',
                'icon' => 'ri-smartphone-line',
                'keywords' => 'web check in link pubblico richiesta prenotazione email whatsapp importa schedina arrivi',
                'route' => 'web_checkin.create',
                'cta' => 'Apri Web Check-in',
                'summary' => 'Permette di preparare un link per il cliente e far compilare online la stessa schedina del sistema.',
                'when' => 'Usa Web Check-in quando vuoi far compilare al cliente i dati prima dell arrivo, riducendo il lavoro della reception al momento del check-in.',
                'result' => 'La reception riceve una pratica gia compilata nel circuito Web Check-in, pronta per essere aperta, completata e poi salvata nel circuito operativo corretto.',
                'items' => [
                    'Richiesta Web Check-in con codice prenotazione',
                    'Link pubblico per il cliente',
                    'Pagina invito e comunicazione esterna',
                    'Apertura della schedina web da parte della reception',
                    'Ricevuta finale stampabile',
                ],
                'details' => [
                    'Il Web Check-in usa la stessa schedina del sistema, ma la fa compilare al cliente da remoto. Cambia il circuito, non cambia la logica dei dati.',
                    'Il codice prenotazione aiuta a collegare la richiesta alla pratica giusta. E un riferimento operativo, utile per reception e proprietario.',
                    'Il cliente compila online e la pratica resta dentro Web Check-in. La reception la apre da li, la completa e solo allora la salva in Arrivi o in Schedina.',
                    'Il sistema prepara link breve, pagina invito, testo email e testo WhatsApp. L invio avviene sempre tramite i programmi esterni del computer o del telefono.',
                    'La ricevuta finale del Web Check-in serve come conferma per il cliente e come prova rapida che il modulo e stato compilato correttamente.',
                ],
            ],
            [
                'slug' => 'questura',
                'title' => 'Questura',
                'icon' => 'ri-shield-check-line',
                'keywords' => 'questura alloggiati txt invio diretto verifica ricevuta storico',
                'route' => 'questura.index',
                'cta' => 'Apri Questura',
                'summary' => 'Genera il file ufficiale TXT per Alloggiati Web e gestisce lo storico degli invii.',
                'when' => 'Usa Questura quando devi controllare le schedine del periodo, scaricare il file ufficiale oppure inviarlo direttamente se la struttura e configurata.',
                'result' => 'Hai un pannello unico per verificare, scaricare, inviare e controllare lo storico di tutto cio che e stato trasmesso.',
                'items' => [
                    'Scarica TXT ufficiale',
                    'Verifica invio diretto',
                    'Invia direttamente',
                    'Storico export e ricevute',
                ],
                'details' => [
                    'Questura controlla prima le schedine del periodo e ti segnala quali sono esportabili e quali no. Se qualcosa manca, il sistema ti porta a correggere i dati.',
                    'Scarica TXT ufficiale produce il file manuale da caricare nel portale Alloggiati Web. Questo resta sempre il canale di sicurezza se l invio diretto non e attivo.',
                    'Verifica invio diretto e Invia direttamente usano il collegamento elettronico. Conviene sempre verificare prima di inviare, cosi vedi eventuali errori prima della trasmissione definitiva.',
                    'Storico export e storico invii ti permettono di capire cosa hai gia scaricato, cosa hai gia inviato e se esiste una ricevuta o un esito da conservare.',
                    'Scarica tabelle ufficiali salva anche uno snapshot delle codifiche ufficiali del servizio Alloggiati Web, senza sporcare le tabelle operative protette del sistema.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Come si usa il pannello',
                        'items' => [
                            'Periodo: scegli il giorno o l intervallo che vuoi inviare o controllare.',
                            'Schedine esportabili: ti dice quante pratiche del periodo sono davvero pronte per Questura.',
                            'Schedine da correggere: ti avvisa subito se qualcosa blocca il file ufficiale o l invio diretto.',
                        ],
                    ],
                    [
                        'title' => 'Azioni principali',
                        'items' => [
                            'Scarica TXT Questura: genera il file ufficiale manuale da caricare nel portale.',
                            'Verifica invio diretto: controlla il pacchetto prima dell invio reale.',
                            'Invia direttamente: usa il collegamento elettronico della struttura, se configurato.',
                            'Storico export e storico invii: servono per sapere cosa e gia stato fatto e cosa manca.',
                            'Scarica tabelle ufficiali: aggiorna e conserva le codifiche ufficiali del servizio.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'tavola-a',
                'title' => 'Tabella A Emilia-Romagna',
                'icon' => 'ri-bar-chart-box-line',
                'keywords' => 'tabella a emilia-romagna istat ross1000 xml invio diretto riepilogo giornaliero statistiche',
                'route' => 'istat.tabella_a.index',
                'cta' => 'Apri Tabella A Emilia-Romagna',
                'summary' => 'Gestisce il controllo statistico mensile, il riepilogo giornaliero e il tracciato XML Ross1000 per la regione Emilia-Romagna.',
                'when' => 'Usa Tabella A Emilia-Romagna per controllare il mese, leggere il riepilogo giornaliero dell hotel, preparare il tracciato XML e gestire l invio statistico regionale.',
                'result' => 'Puoi verificare il mese, scaricare l XML ufficiale e tenere uno storico chiaro di export e invii diretti.',
                'items' => [
                    'Riepilogo giornaliero',
                    'Scarica XML ufficiale',
                    'Verifica invio diretto',
                    'Invia direttamente ed esito',
                    'Stampa riepilogo hotel',
                ],
                'details' => [
                    'Tabella A Emilia-Romagna lavora sul movimento statistico della struttura. Per questo usa sia le schedine definitive sia i dati di struttura, come camere disponibili, letti disponibili e periodo di apertura.',
                    'Il riepilogo giornaliero e un prospetto chiuso: non modifica nulla. Serve a verificare giorno per giorno presenti, arrivi, partenze, italiani, stranieri, camere occupate e disponibilita.',
                    'Scarica XML ufficiale produce il file da usare per il caricamento manuale. Verifica invio diretto e Invia direttamente usano lo stesso XML sul collegamento Ross1000.',
                    'Stampa riepilogo hotel produce una vista sintetica utile per il proprietario e per il controllo interno della struttura.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Controllo del mese',
                        'items' => [
                            'Mese e periodo: definiscono il blocco statistico che stai preparando.',
                            'Riepilogo giornaliero: ti mostra se ogni giorno del mese e coerente con i movimenti della struttura.',
                            'Dati struttura: camere disponibili, letti disponibili e apertura influenzano il risultato finale del mese.',
                        ],
                    ],
                    [
                        'title' => 'Output e invio',
                        'items' => [
                            'Scarica XML Tabella A: produce il file ufficiale da usare per il caricamento manuale regionale.',
                            'Verifica invio diretto e Invia direttamente: usano il canale elettronico Ross1000 quando configurato.',
                            'Esito e ricevuta operativa: permettono di conservare la prova della verifica o dell invio.',
                            'Stampa riepilogo hotel: serve per il controllo gestionale della struttura.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'presenze',
                'title' => 'Presenze',
                'icon' => 'ri-line-chart-line',
                'keywords' => 'presenze arrivi partenze occupazione italiani stranieri oggi dettaglio riepilogo',
                'route' => 'presenze.index',
                'cta' => 'Apri presenze',
                'summary' => 'Mostra riepiloghi, dettagli, situazione giornaliera, arrivi, partenze e occupazione.',
                'when' => 'Usa Presenze quando devi controllare l andamento dell hotel, sapere quante persone hai in struttura o preparare numeri utili per amministrazione e commercialista.',
                'result' => 'Ottieni subito riepiloghi mensili, dettagli del periodo, situazione giornaliera, arrivi, partenze e occupazione.',
                'items' => [
                    'Riepilogo mensile',
                    'Dettaglio periodo',
                    'Situazione giornaliera',
                    'Arrivi / Partenze e Occupazione',
                ],
                'details' => [
                    'Riepilogo mensile ti fa vedere l andamento del periodo, con separazione tra italiani e stranieri. E utile per amministrazione, commercialista e controllo generale.',
                    'Dettaglio periodo mostra le singole schedine del periodo e ti aiuta a controllare una pratica per volta, con numero schedina, provenienza, adulti, minori e presenze.',
                    'Situazione giornaliera e la fotografia di un giorno preciso. Ti dice quante persone sono presenti, chi entra, chi esce e quante camere risultano occupate.',
                    'Occupazione confronta camere e letti occupati rispetto alla disponibilita totale della struttura. E il tab piu utile per capire rendimento e carico della struttura.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Lettura dei report',
                        'items' => [
                            'Riepilogo mensile: utile per vedere l andamento del mese o dell anno in modo sintetico.',
                            'Dettaglio periodo: utile quando devi scendere nella singola schedina e capire da dove nasce un numero.',
                            'Situazione giornaliera: utile per sapere quante persone sono in struttura in un giorno preciso.',
                        ],
                    ],
                    [
                        'title' => 'Controllo operativo',
                        'items' => [
                            'Arrivi / Partenze: mostra chi entra e chi esce nel periodo selezionato.',
                            'Occupazione: confronta camere e letti occupati con la disponibilita reale della struttura.',
                            'Italiani e stranieri: aiuta a separare le provenienze per lettura gestionale e statistica.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'tassa-soggiorno',
                'title' => 'Tassa di soggiorno',
                'icon' => 'ri-money-euro-circle-line',
                'keywords' => 'tassa soggiorno esenzioni rapporto csv comune imposta notti 777',
                'route' => 'tassa_di_soggiorno.edit',
                'cta' => 'Apri tassa di soggiorno',
                'summary' => 'Calcola e registra la tassa di soggiorno secondo le regole della struttura e del comune.',
                'when' => 'Usa Tassa di soggiorno quando devi controllare gli importi per ogni schedina, applicare esenzioni e produrre i riepiloghi da consegnare al comune o al commercialista.',
                'result' => 'Hai un calcolo coerente con le regole del comune e puoi controllare facilmente notti imponibili, esenzioni e riepiloghi.',
                'items' => [
                    'Configurazione importi e regole',
                    'Esenzioni',
                    'Rapporto per schedina e per persona',
                    'CSV Comune e CSV interno',
                    'Stampa A4 mensile',
                    'Notti imponibili e notti oltre max con codice 777',
                ],
                'details' => [
                    'La tassa di soggiorno dipende dalle regole del comune e dalla struttura. Per questo va configurata una sola volta e poi controllata periodicamente.',
                    'Le esenzioni servono a escludere correttamente i casi previsti dal regolamento comunale. E importante tenerle chiare per non sbagliare il calcolo.',
                    'Il rapporto mensile puo essere letto per schedina oppure per persona. Questo aiuta sia il controllo amministrativo sia il controllo analitico.',
                    'CSV Comune e CSV interno sono separati: il primo serve all invio o al caricamento ufficiale, il secondo al controllo gestionale e del commercialista.',
                    'La stampa A4 mensile mostra riepilogo, dettaglio, esenzioni ed epilogo finale con il totale da versare.',
                    'Il codice 777 viene usato per le notti oltre il massimo imponibile. Le notti restano conteggiate come soggiorno, ma non rientrano piu nell importo da pagare.',
                ],
            ],
            [
                'slug' => 'notifiche',
                'title' => 'Notifiche',
                'icon' => 'ri-notification-3-line',
                'keywords' => 'notifiche campanita centro notifiche compleanni consegne presenti in casa',
                'route' => 'notifiche.index',
                'cta' => 'Apri notifiche',
                'summary' => 'Raccoglie le notifiche interne del sistema e gli avvisi automatici utili alla reception.',
                'when' => 'Usa Notifiche quando vuoi vedere in un solo posto consegne, avvisi interni e compleanni degli ospiti presenti in struttura.',
                'result' => 'Hai una lettura rapida di cio che richiede attenzione oggi, sia dalla campanita sia dal centro notifiche.',
                'items' => [
                    'Campanita topbar',
                    'Centro notifiche',
                    'Consegne interne',
                    'Compleanni in casa',
                ],
                'details' => [
                    'La campanita in alto mostra le notifiche piu recenti e permette di aprire subito il dettaglio senza uscire dalla pagina in cui stai lavorando.',
                    'Il centro notifiche raccoglie le notifiche registrate e distingue stato, priorita e mittente.',
                    'I compleanni in casa compaiono solo se la persona compie gli anni oggi ed e realmente presente in struttura nel periodo di soggiorno.',
                    'Per i compleanni il sistema mostra anche scheda, arrivo, partenza e origine operativa, cosi la reception puo leggere subito il contesto.',
                ],
            ],
            [
                'slug' => 'utenti-consegne',
                'title' => 'Utenti e consegne',
                'icon' => 'ri-user-settings-line',
                'keywords' => 'utenti consegne reception proprietario profilo password messaggi turni accessi attivita campanita notifiche',
                'route' => 'gestione.operativa.index',
                'cta' => 'Apri utenti e consegne',
                'summary' => 'Gestisce chi entra nel programma, il profilo personale, le consegne di turno e lo storico attività.',
                'when' => 'Usa Utenti e consegne quando il proprietario deve creare le persone autorizzate, cambiare password oppure quando la reception deve leggere e lasciare comunicazioni di turno.',
                'result' => 'Sai sempre chi ha lavorato, cosa ha fatto e quali consegne sono ancora aperte per il turno successivo.',
                'items' => [
                    'Utenti gia creati e nuovo utente',
                    'Profilo personale',
                    'Consegne di turno',
                    'Entrate / uscite e attività svolte',
                ],
                'details' => [
                    'Il proprietario crea le persone autorizzate a entrare nel programma. Tutti usano lo stesso nome di accesso della struttura, ma ognuno ha la propria password personale.',
                    'Il profilo personale mostra come la persona appare nel sistema e quali contatti puo lasciare per emergenze o comunicazioni fuori servizio.',
                    'Le consegne servono per il cambio turno. Una persona lascia le informazioni importanti e la successiva puo leggerle, rispondere o chiuderle quando il tema e risolto.',
                    'Entrate / uscite e attivita ti aiutano a ricostruire chi ha lavorato, in quale parte del programma e in quale fascia oraria.',
                    'Attivita mostra anche scheda, origine operativa e contesto quando l azione riguarda Schedine, Arrivi o Web Check-in.',
                    'La campanita in alto mostra le notifiche interne, mentre il supporto online ha ora il suo spazio separato in topbar.',
                ],
            ],
            [
                'slug' => 'supporto-online',
                'title' => 'Centro di supporto / Supporto online',
                'icon' => 'ri-customer-service-2-line',
                'keywords' => 'centro di supporto supporto online ticket admin struttura priorita stato risposte assistenza',
                'route' => 'supporto.index',
                'cta' => 'Apri supporto online',
                'summary' => 'Raccoglie i ticket di assistenza tra struttura e amministratore del software, in modo separato dall aiuto, dalle notifiche e dai manuali.',
                'when' => 'Usa Centro di supporto quando hai un problema tecnico o operativo che non si risolve con il manuale e devi chiedere aiuto all amministratore del software.',
                'result' => 'Ogni richiesta resta tracciata per struttura, con priorita, stato, assegnazione amministrativa, conversazione e storico delle risposte.',
                'items' => [
                    'Supporto online',
                    'Nuovo ticket supporto',
                    'Ticket aperti',
                    'Storico ticket chiusi',
                    'Risposte del supporto',
                    'Stati, priorita e non letti',
                ],
                'details' => [
                    'Supporto online e separato da Aiuto: qui non trovi manuali, ma richieste reali di assistenza verso l amministratore del software.',
                    'Ogni ticket appartiene sempre alla singola struttura che lo apre. Qualunque utente operativo della struttura puo aprire il ticket, ma resta sempre tracciato chi lo ha emesso.',
                    'Le priorita aiutano a segnalare l urgenza del problema, mentre lo stato fa capire se il ticket e aperto, in lavorazione, in attesa della struttura o chiuso.',
                    'Quando arriva una risposta del supporto, la struttura la ritrova nel ticket e puo seguirla fino alla chiusura del caso.',
                    'La topbar ha un accesso dedicato al supporto, separato dalla campanita delle notifiche. Da li puoi vedere i ticket attivi e quelli con nuove risposte.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Come si apre un ticket',
                        'items' => [
                            'Titolo: descrive in poche parole il problema.',
                            'Categoria: aiuta a capire se il problema riguarda clienti, schedine, invii telematici, configurazioni o altro.',
                            'Priorita: distingue i casi normali da quelli urgenti.',
                            'Modulo o pagina coinvolta: aiuta il supporto a capire subito dove stavi lavorando.',
                            'Descrizione: deve spiegare cosa non funziona, cosa stavi facendo e quale risultato ti aspettavi.',
                        ],
                    ],
                    [
                        'title' => 'Come si legge un ticket',
                        'items' => [
                            'Ticket n.: e il codice della richiesta e serve a ritrovarla facilmente.',
                            'Oggetto: riassume il contenuto del ticket.',
                            'Emesso da: dice quale persona della struttura ha chiesto il supporto.',
                            'Gestione admin: fa vedere se il ticket e gia preso in carico e da chi viene seguito.',
                            'Stato: mostra se il caso e ancora aperto, in lavorazione, in attesa della struttura o chiuso.',
                            'Conversazione: contiene tutte le risposte scambiate tra struttura e amministratore del software.',
                            'Messaggi da leggere: aiutano a capire subito se e arrivata una nuova risposta.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'cestino',
                'title' => 'Cestino',
                'icon' => 'ri-delete-bin-6-line',
                'keywords' => 'cestino eliminati ripristina elimina definitivo storico recupero',
                'route' => 'cestino.index',
                'cta' => 'Apri cestino',
                'summary' => 'Raccoglie i dati eliminati dal sistema e permette di ripristinarli o eliminarli in modo definitivo.',
                'when' => 'Usa Cestino quando devi recuperare qualcosa eliminato per errore oppure quando vuoi controllare e svuotare in modo definitivo i dati non piu necessari.',
                'result' => 'Riduci il rischio di perdita dati e hai sempre uno storico centrale di cio che e stato eliminato.',
                'items' => [
                    'Filtro per sezione',
                    'Ricerca generale',
                    'Ripristina',
                    'Elimina definitivamente',
                ],
            ],
            [
                'slug' => 'configurazioni',
                'title' => 'Configurazioni',
                'icon' => 'ri-settings-3-line',
                'keywords' => 'configurazioni gruppi titoli tipo cliente tipo documento tipo alloggiato rilasciato via',
                'summary' => 'Contiene le tabelle e le librerie usate dal sistema per compilare i dati in modo coerente.',
                'when' => 'Usa Configurazioni quando devi controllare o aggiornare le tabelle di lavoro che alimentano select, codici e classificazioni del programma.',
                'result' => 'Mantieni compilazioni coerenti, meno errori manuali e una struttura ordinata dei dati in tutti i moduli.',
                'items' => [
                    'Gruppi',
                    'Titoli',
                    'Tipo cliente',
                    'Tipo documento / Tipo alloggiato / Tipo via / Rilasciato da',
                ],
                'details' => [
                    'Configurazioni raccoglie le tabelle che alimentano i campi del sistema. Tenere queste tabelle in ordine significa lavorare piu in fretta e con meno errori.',
                    'I gruppi servono a classificare clienti e pratiche secondo il criterio organizzativo della struttura.',
                    'Titoli, tipi documento, tipi alloggiato, tipi via e rilasciato da vengono usati ogni giorno nei moduli. Per questo devono essere chiari, coerenti e in italiano.',
                    'Molte di queste tabelle sono informative o di sola lettura. Questo evita modifiche accidentali su codici o classificazioni sensibili.',
                ],
            ],
        ];

        $modules = [
            [
                'slug' => 'struttura',
                'title' => 'Dati struttura',
                'icon' => 'ri-building-line',
                'keywords' => 'struttura hotel tipologia classificazione apertura stagionalita geo camere letti credenziali',
                'route' => 'struttura.edit',
                'cta' => 'Apri dati struttura',
                'summary' => 'Qui si imposta l identita generale della struttura e tutto quello che serve per far funzionare correttamente il sistema.',
                'when' => 'Usa Dati struttura all inizio della configurazione oppure ogni volta che cambiano contatti, disponibilita, apertura o credenziali telematiche.',
                'result' => 'La struttura resta coerente in tutti i moduli e i dati vengono ripresi correttamente in report, stampe e invii.',
                'items' => [
                    'Dashboard struttura con riepilogo licenza, conto e alert',
                    'Tipologia generale, tipologia struttura e classificazione',
                    'Dati struttura, contatti e logo',
                    'Apertura e stagionalita',
                    'GEO struttura',
                    'Camere e letti disponibili',
                    'Credenziali Questura e ISTAT / Ross1000',
                ],
                'details' => [
                    'La Dashboard struttura riassume lo stato della struttura: prodotto in uso, licenza principale, prossima scadenza, stato conto, notifiche e ticket aperti.',
                    'Tipologia generale, tipologia struttura e classificazione descrivono la struttura in modo ufficiale e coerente con il resto del sistema.',
                    'Il blocco GEO va compilato con attenzione, perche posizione e comune influenzano stampe, tassa di soggiorno e dati statistici.',
                    'Camere e letti disponibili servono ai report di occupazione e a Tabella A Emilia-Romagna.',
                    'Apertura e stagionalita spiegano se la struttura lavora tutto l anno o solo in un periodo.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Lettura commerciale per la struttura',
                        'items' => [
                            'Relazioni e pagamenti: la struttura vede proprietario, amministratore di riferimento, piano, stato pagamento e scadenza del servizio.',
                            'Licenze e conto: mostra licenze attive, tracking, prezzo, scadenze e documenti collegati a quella struttura.',
                            'I dati commerciali in struttura sono informativi: nascono dal circuito admin, proprietario e superadmin, ma qui restano sempre consultabili.',
                        ],
                    ],
                    [
                        'title' => 'Identita e classificazione',
                        'items' => [
                            'Tipologia generale: definisce la famiglia principale della struttura.',
                            'Tipologia struttura: indica il tipo reale della struttura dentro la famiglia scelta.',
                            'Classificazione: mantiene coerente la categoria ufficiale della struttura.',
                            'Nome struttura e ragione sociale: servono per gestione interna, stampe e amministrazione.',
                        ],
                    ],
                    [
                        'title' => 'Apertura e disponibilita',
                        'items' => [
                            'Tipo apertura: indica se la struttura e annuale o stagionale.',
                            'Data apertura e data chiusura: servono soprattutto se la struttura e stagionale.',
                            'Camere disponibili e letti disponibili: vanno tenuti allineati con la disponibilita reale.',
                        ],
                    ],
                    [
                        'title' => 'Posizione e credenziali',
                        'items' => [
                            'GEO struttura: usa prima CAP o comune quando possibile.',
                            'Telefono, email e sito: servono per contatti e documenti del sistema.',
                            'Credenziali telematiche: si compilano solo quando vuoi usare invii diretti o preparare la struttura ai servizi ufficiali.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'configurazioni',
                'title' => 'Configurazioni',
                'icon' => 'ri-settings-3-line',
                'keywords' => 'configurazioni gruppi titoli tipo cliente tipo documento tipo alloggiato rilasciato tipo via',
                'summary' => 'Qui stanno le tabelle di lavoro che alimentano select, classificazioni e campi del programma.',
                'when' => 'Usa Configurazioni quando devi controllare o aggiornare le librerie del sistema.',
                'result' => 'Il lavoro resta coerente e i campi dei moduli principali si compilano in modo uniforme.',
                'items' => [
                    'Gruppi',
                    'Titoli',
                    'Tipo cliente',
                    'Tipo alloggiato',
                    'Tipo documenti',
                    'Rilasciato da',
                    'Tipo via',
                    'Tassa di soggiorno',
                ],
                'details' => [
                    'Configurazioni non e un modulo operativo giornaliero: prepara il terreno per lavorare meglio nei moduli principali.',
                    'Molte tabelle sono informative o protette, cosi non si modificano codici sensibili per errore.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Tabelle anagrafiche',
                        'items' => [
                            'Gruppi e titoli servono a classificare clienti e persone.',
                            'Tipo cliente distingue i diversi ruoli usati nel sistema.',
                        ],
                    ],
                    [
                        'title' => 'Tabelle documentali e operative',
                        'items' => [
                            'Tipo alloggiato, tipo documenti, rilasciato da e tipo via alimentano i select usati nelle compilazioni quotidiane.',
                            'Tassa di soggiorno vive qui perche e una configurazione di base della struttura.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'clienti',
                'title' => 'Clienti',
                'icon' => 'ri-team-line',
                'keywords' => 'clienti anagrafica residenza documento azienda bozza',
                'route' => 'customers',
                'cta' => 'Apri clienti',
                'summary' => 'Raccoglie le persone registrate dalla struttura e prepara i dati che poi entrano nelle schedine.',
                'when' => 'Usa Clienti quando devi creare una persona, correggere i suoi dati o recuperare una scheda anagrafica gia registrata.',
                'result' => 'Le informazioni del cliente possono essere riutilizzate senza riscrivere tutto ogni volta.',
                'items' => [
                    'Dati base del cliente',
                    'Anagrafica e residenza',
                    'Documento',
                    'Azienda, se presente',
                    'Salvataggio in bozza',
                ],
                'details' => [
                    'Il cliente e la base anagrafica della persona. Conviene compilarlo bene una volta e riusarlo poi nelle schedine.',
                    'Tipo cliente e gruppi aiutano a classificare le persone secondo la logica della struttura.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Blocchi principali',
                        'items' => [
                            'Dati base: nome, cognome, sesso, tipo cliente e gruppi.',
                            'Geo anagrafica e geo residenza: servono a separare correttamente nascita e residenza.',
                            'Documento: va compilato bene quando il cliente serve per schedine e invii ufficiali.',
                            'Bozza: utile quando non hai ancora tutti i dati disponibili.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'schedine',
                'title' => 'Schedine',
                'icon' => 'ri-file-list-3-line',
                'keywords' => 'schedine arrivi bozze web check in componenti soggiorno',
                'route' => 'schedina',
                'cta' => 'Apri schedine',
                'summary' => 'Raccoglie il soggiorno vero e proprio: ospite principale, componenti, permanenza, documento e dati collegati.',
                'when' => 'Usa Schedine quando il soggiorno entra nel circuito operativo della struttura.',
                'result' => 'La schedina diventa il punto centrale per reception, statistiche, tassa di soggiorno e invii.',
                'items' => [
                    'Schedine ufficiali',
                    'Schedine arrivi',
                    'Schedine bozze',
                    'Web Check-in',
                    'Tassa di soggiorno',
                ],
                'details' => [
                    'Schedine ufficiali, Arrivi e Bozze fanno parte dello stesso ciclo della pratica, ma ognuno resta nel proprio circuito fino al momento giusto.',
                    'Web Check-in usa la stessa logica dati, ma resta in un circuito separato finche la reception non apre la schedina web e decide come salvarla.',
                    'Quantita persone, quantita camere e quantita letti sono dati importanti per statistiche e occupazione.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Circuiti della pratica',
                        'items' => [
                            'Schedina ufficiale: pratica completa nel circuito principale.',
                            'Bozze: pratica salvata ma non ancora completa.',
                            'Arrivi: pratica preparatoria prima della schedina ufficiale.',
                            'Web Check-in: pratica compilata dal cliente online che resta nel circuito web fino all intervento della reception.',
                        ],
                    ],
                    [
                        'title' => 'Contenuto della schedina',
                        'items' => [
                            'Ospite principale: dati completi della persona principale del soggiorno.',
                            'Componenti: persone aggiuntive della stessa pratica.',
                            'Arrivo, partenza, quantita persone, camere e letti: governano presenze, partenze e occupazione.',
                            'Documento e residenza: servono ai controlli ufficiali e ai dati di registrazione.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'invio-telematico',
                'title' => 'Invio telematico',
                'icon' => 'ri-send-plane-line',
                'keywords' => 'questura tavola a tassa soggiorno txt xml invio diretto storico',
                'summary' => 'Qui si trovano i pannelli ufficiali di invio e controllo verso Questura, Tabella A Emilia-Romagna e i riepiloghi della tassa di soggiorno.',
                'when' => 'Usa Invio telematico quando devi produrre un file ufficiale, controllare un periodo o tentare un invio diretto.',
                'result' => 'Hai in un solo posto i flussi verso esterno e gli storici di quello che hai gia scaricato o trasmesso.',
                'items' => [
                    'Questura',
                    'Tabella A Emilia-Romagna',
                    'Tassa di soggiorno',
                ],
                'details' => [
                    'Questura genera il TXT ufficiale per Alloggiati Web e puo usare anche il canale diretto quando la struttura e pronta.',
                    'Tabella A Emilia-Romagna genera l XML regionale, controlla il mese e conserva verifica, invio ed esito.',
                    'Tassa di soggiorno produce il rapporto mensile, la stampa A4 e i due CSV separati per uso ufficiale e gestionale.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Questura',
                        'items' => [
                            'Controlla quali schedine del periodo sono esportabili.',
                            'Scarica il TXT ufficiale oppure usa verifica e invio diretto.',
                            'Consulta storico export, storico invii e ricevute.',
                        ],
                    ],
                    [
                        'title' => 'Tabella A Emilia-Romagna',
                        'items' => [
                            'Controlla il mese e il riepilogo giornaliero.',
                            'Scarica l XML ufficiale oppure usa verifica e invio diretto.',
                            'Consulta storico XML, storico invii ed esito operativo.',
                        ],
                    ],
                    [
                        'title' => 'Tassa di soggiorno',
                        'items' => [
                            'Controlla regole, esenzioni, rapporto per schedina e per persona.',
                            'Verifica notti imponibili e notti oltre soglia.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'statistica',
                'title' => 'Statistica',
                'icon' => 'ri-line-chart-line',
                'keywords' => 'presenze arrivi partenze occupazione italiani stranieri',
                'summary' => 'Qui si controlla l andamento del lavoro della struttura con report di presenze, arrivi, partenze e occupazione.',
                'when' => 'Usa Statistica quando devi leggere i numeri della struttura per reception, proprietario o commercialista.',
                'result' => 'Ottieni una lettura chiara dei movimenti e dell occupazione della struttura.',
                'items' => [
                    'Presenze',
                    'Riepilogo mensile',
                    'Dettaglio periodo',
                    'Situazione giornaliera',
                    'Arrivi / Partenze',
                    'Occupazione',
                ],
                'details' => [
                    'La sezione principale di Statistica e Presenze. Da li si aprono i diversi modi di leggere il periodo o il giorno.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Report principali',
                        'items' => [
                            'Riepilogo mensile: visione sintetica del mese o dell anno.',
                            'Dettaglio periodo: visione analitica schedina per schedina.',
                            'Situazione giornaliera: foto di un giorno preciso.',
                            'Arrivi / Partenze e Occupazione: controllo operativo e gestionale.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'calendario',
                'title' => 'Calendario',
                'icon' => 'ri-calendar-event-line',
                'keywords' => 'calendario agenda personale struttura storico note compleanni filtro ricerca mese giorno',
                'route' => 'calendario.index',
                'cta' => 'Apri calendario',
                'summary' => 'Organizza agenda personale, agenda struttura, note operative, storico e automatismi del sistema.',
                'when' => 'Usa Calendario quando devi annotare un impegno, cercare una nota, leggere lo storico oppure controllare compleanni, promemoria e appuntamenti della struttura.',
                'result' => 'Hai un calendario leggibile per ruolo: la struttura vede il proprio lavoro, mentre proprietario, admin e superadmin possono vedere anche il quadro aggregato del loro perimetro.',
                'items' => [
                    'Calendario struttura',
                    'Calendario personale',
                    'Vista mese e giorno',
                    'Filtro rapido di ricerca',
                    'Storico note chiuse',
                    'Compleanni automatici di clienti e componenti',
                ],
                'details' => [
                    'Il calendario della struttura riguarda la struttura corrente: non serve scegliere una struttura quando stai gia lavorando dentro una struttura precisa.',
                    'Il calendario personale resta separato da quello della struttura e serve per note private del ruolo corrente.',
                    'Proprietario, admin e superadmin possono leggere sia il proprio calendario personale sia un calendario aggregato delle strutture del loro perimetro.',
                    'Il filtro rapido cerca parole chiave dentro note, titoli, descrizioni, struttura e automatismi operativi.',
                    'I compleanni vengono creati in automatico solo quando cliente o componente risultano davvero presenti nel giorno del compleanno.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Come si usa',
                        'items' => [
                            'Data di riferimento: sceglie il giorno base da cui leggere il calendario.',
                            'Vista mese / giorno: permette di passare da una lettura ampia a una piu dettagliata.',
                            'Struttura: compare solo quando il ruolo puo vedere piu strutture.',
                            'Filtro rapido: aiuta a trovare note, eventi, nominativi e riferimenti operativi senza cambiare modulo.',
                        ],
                    ],
                    [
                        'title' => 'Cosa contiene',
                        'items' => [
                            'Note manuali della struttura o personali.',
                            'Automatismi del sistema, come compleanni e altri promemoria legati al lavoro reale.',
                            'Storico delle note chiuse, utile per ritrovare comunicazioni gia gestite.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'utenti-consegne',
                'title' => 'Utenti e consegne',
                'icon' => 'ri-user-settings-line',
                'keywords' => 'utenti consegne proprietario reception profilo attivita',
                'route' => 'gestione.operativa.index',
                'cta' => 'Apri utenti e consegne',
                'summary' => 'Gestisce le persone che entrano nel programma, il loro profilo e il passaggio di consegne tra un turno e l altro.',
                'when' => 'Usa questa sezione quando il proprietario deve creare utenti o quando il turno deve lasciare comunicazioni alla persona successiva.',
                'result' => 'Sai chi ha lavorato, cosa ha fatto e quali consegne sono ancora aperte.',
                'items' => [
                    'Utenti',
                    'Profilo',
                    'Consegne',
                    'Entrate / uscite',
                    'Attivita',
                ],
                'details' => [
                    'Tutte le persone usano lo stesso nome di accesso della struttura, ma ognuna ha la propria password personale.',
                    'Le consegne permettono di gestire il cambio turno senza perdere informazioni.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Gestione persone',
                        'items' => [
                            'Il proprietario crea e aggiorna chi puo entrare nel programma.',
                            'Ogni persona ha il suo nome visibile, il suo ruolo e la sua password personale.',
                        ],
                    ],
                    [
                        'title' => 'Passaggio di turno',
                        'items' => [
                            'Le consegne aperte si leggono all inizio del turno.',
                            'Le consegne possono essere aperte, viste o chiuse.',
                            'Lo storico aiuta a capire chi ha lavorato e dove.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'cestino',
                'title' => 'Cestino',
                'icon' => 'ri-delete-bin-6-line',
                'keywords' => 'cestino ripristina elimina storico',
                'route' => 'cestino.index',
                'cta' => 'Apri cestino',
                'summary' => 'Raccoglie tutto cio che viene eliminato dal sistema e permette di recuperarlo o eliminarlo in modo definitivo.',
                'when' => 'Usa Cestino quando devi recuperare qualcosa eliminato per errore o controllare lo storico delle eliminazioni.',
                'result' => 'Riduci il rischio di perdita dati e hai un archivio centrale di quello che e stato eliminato.',
                'items' => [
                    'Ricerca',
                    'Filtro per sezione',
                    'Ripristina',
                    'Elimina definitivamente',
                ],
            ],
        ];

        $faqs = [
            [
                'question' => 'Come entra una persona della reception?',
                'answer' => 'Tutte le persone della stessa struttura usano lo stesso nome di accesso. Cambia solo la password personale. Il proprietario crea gli utenti nella sezione Utenti e consegne.',
                'keywords' => 'login reception password utente proprietario accesso',
            ],
            [
                'question' => 'Quando uso Arrivi e quando uso Schedine?',
                'answer' => 'Usa Arrivi quando stai preparando una pratica che non vuoi ancora far entrare nel circuito ufficiale delle schedine. Usa Schedine quando la pratica è già nel circuito principale della struttura.',
                'keywords' => 'arrivi schedine differenza importa',
            ],
            [
                'question' => 'Come funziona il Web Check-in?',
                'answer' => 'Crei una richiesta Web Check-in, copi o invii il link al cliente e lui compila la scheda online. La pratica resta nel circuito Web Check-in finche la reception non apre la schedina web, la completa e la salva nel circuito operativo corretto.',
                'keywords' => 'web check in link cliente importa',
            ],
            [
                'question' => 'Come faccio a capire se una consegna è ancora aperta?',
                'answer' => 'Nella scheda Consegne trovi prima le consegne aperte. Lo stato può essere Da vedere, Vista o Chiusa.',
                'keywords' => 'consegne aperta vista chiusa stato',
            ],
            [
                'question' => 'Quando uso Aiuto e quando Supporto online?',
                'answer' => 'Aiuto serve per leggere manuali, procedure e spiegazioni del programma. Supporto online serve invece per aprire un ticket reale verso l amministratore del software quando hai un problema da risolvere.',
                'keywords' => 'aiuto supporto online differenza ticket manuale',
            ],
            [
                'question' => 'Chi può aprire un ticket di supporto?',
                'answer' => 'Qualunque persona operativa della struttura può aprire un ticket. Il ticket resta sempre legato alla struttura e il sistema tiene traccia di chi lo ha emesso.',
                'keywords' => 'ticket supporto chi puo aprire struttura utente',
            ],
            [
                'question' => 'Dove controllo proforme, pagamenti e licenze?',
                'answer' => 'Nel circuito amministrativo. L admin li vede solo per il proprio perimetro, mentre il superadmin li vede per tutto il sistema. Da li puoi aprire la licenza, vedere la proforma e segnare il documento come pagato con data e numero fattura.',
                'keywords' => 'proforme pagamenti licenze admin superadmin fattura pagato',
            ],
            [
                'question' => 'Il file Questura che scarico è quello ufficiale?',
                'answer' => 'Sì. Il TXT scaricato dalla schermata Questura è il file ufficiale da caricare manualmente nel portale Alloggiati Web, salvo credenziali attive per l invio diretto.',
                'keywords' => 'questura txt ufficiale download',
            ],
            [
                'question' => 'La Tabella A Emilia-Romagna si scarica in TXT o in XML?',
                'answer' => 'La Tabella A Emilia-Romagna si scarica in XML. Lo stesso tracciato viene usato anche per l invio diretto quando la configurazione ISTAT / Ross1000 e completa.',
                'keywords' => 'tavola a xml istat ross1000',
            ],
            [
                'question' => 'Le notifiche e il supporto sono la stessa cosa?',
                'answer' => 'No. Notifiche raccoglie avvisi interni del sistema e compleanni in casa. Supporto online raccoglie invece i ticket verso l amministratore del software. In topbar sono separati proprio per non mescolare i due circuiti.',
                'keywords' => 'notifiche supporto differenza campanita ticket',
            ],
            [
                'question' => 'Posso salvare una schedina incompleta?',
                'answer' => 'Sì. Puoi salvarla come bozza o in altri circuiti operativi. Quando la porti in schedina ufficiale, i dati obbligatori devono essere completi.',
                'keywords' => 'schedina bozza incompleta salva',
            ],
            [
                'question' => 'Dove vedo quante persone ho oggi in hotel?',
                'answer' => 'Apri Statistica > Presenze e usa il tab Situazione giornaliera. Puoi scegliere oggi oppure un giorno storico.',
                'keywords' => 'oggi hotel persone presenti situazione giornaliera',
            ],
            [
                'question' => 'Come funziona il calendario del sistema?',
                'answer' => 'La struttura vede il proprio calendario struttura e il calendario personale. Proprietario, admin e superadmin vedono anche il calendario aggregato delle strutture del loro perimetro. Il filtro rapido ti aiuta a trovare note, compleanni, check-in e altri riferimenti operativi.',
                'keywords' => 'calendario personale struttura aggregato filtro compleanni',
            ],
        ];

        $personas = [
            [
                'title' => 'Per Reception',
                'icon' => 'ri-user-voice-line',
                'keywords' => 'reception clienti schedine arrivi web check in consegne presenze tassa soggiorno',
                'summary' => 'La reception usa il programma durante il turno per registrare persone, controllare i movimenti e lasciare consegne al turno successivo.',
                'result' => 'A fine turno il lavoro resta leggibile, continuabile e tracciato per chi entra dopo.',
                'steps' => [
                    'Entra con il nome di accesso della struttura e con la tua password personale.',
                    'Controlla prima le consegne aperte del turno precedente.',
                    'Crea o aggiorna clienti e schedine.',
                    'Usa Arrivi, Bozze e Web Check-in secondo il punto in cui si trova davvero la pratica.',
                    'Controlla Presenze, Arrivi / Partenze e Situazione giornaliera per sapere chi entra, chi esce e chi è in struttura.',
                    'Se il problema non si risolve da sola, apri un ticket in Supporto online.',
                ],
                'details' => [
                    'La reception non configura le tabelle del programma, ma lavora ogni giorno sui moduli operativi e sulle consegne di turno.',
                    'Ogni persona entra con la propria password personale, anche se il nome di accesso della struttura resta uguale per tutti.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Profilo personale',
                        'items' => [
                            'Ogni persona puo aggiornare il proprio nome visibile, i recapiti e gli altri dati personali mostrati nel sistema.',
                            'Il profilo serve anche per far capire subito chi sta lavorando in quel momento.',
                        ],
                    ],
                    [
                        'title' => 'Consegne di turno',
                        'items' => [
                            'La reception legge prima le consegne aperte lasciate dal turno precedente.',
                            'Una consegna puo essere aperta, vista o chiusa.',
                            'Se serve, la reception puo rispondere a una consegna e lasciarla ancora aperta per il turno successivo.',
                        ],
                    ],
                    [
                        'title' => 'Entrate / uscite e attivita',
                        'items' => [
                            'L entrata si registra quando la persona accede al programma con la sua password.',
                            'L uscita si registra quando la persona esce dal programma.',
                            'Le attivita mostrano in quale parte del sistema la persona ha lavorato e cosa ha fatto in modo sintetico.',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Per Proprietario',
                'icon' => 'ri-admin-line',
                'keywords' => 'proprietario struttura utenti credenziali questura tavola a configurazioni controllo',
                'summary' => 'Il proprietario configura il sistema, crea le persone autorizzate e controlla le parti sensibili del programma.',
                'result' => 'La struttura resta ordinata, le credenziali sono sotto controllo e i flussi telematici possono essere gestiti senza dipendere da una sola persona.',
                'steps' => [
                    'Crea gli utenti della struttura in Utenti e consegne.',
                    'Configura la struttura, le credenziali Questura e Tabella A Emilia-Romagna e i dati di apertura.',
                    'Controlla gli invii telematici e lo storico dei file scaricati o inviati.',
                    'Verifica Presenze, Occupazione e Tabella A Emilia-Romagna per controllare l andamento della struttura.',
                    'Usa Supporto online quando serve l intervento dell amministratore del software.',
                    'Usa il Cestino per recuperare dati eliminati per errore.',
                ],
                'details' => [
                    'Il proprietario gestisce la parte sensibile della struttura: persone autorizzate, credenziali, configurazioni e controlli finali.',
                    'La sezione Utenti e consegne e il punto centrale per organizzare chi puo entrare nel programma e come avviene il passaggio di turno.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Utenti',
                        'items' => [
                            'Il proprietario crea le persone autorizzate che possono entrare nel programma della struttura.',
                            'Il nome di accesso della struttura resta uguale per tutti; quello che cambia e la password personale di ciascuno.',
                            'Per ogni persona si possono registrare nome, nome visibile, ruolo, telefono, email di emergenza e stato attivo o non attivo.',
                            'Quando una persona non deve piu usare il sistema, conviene disattivarla invece di cancellarla subito.',
                        ],
                    ],
                    [
                        'title' => 'Password e profili',
                        'items' => [
                            'La password personale si cambia dalla gestione utenti con il comando dedicato, non direttamente nella lista.',
                            'Il profilo personale permette di vedere e aggiornare i dati che compaiono in alto nel programma.',
                            'Il ruolo serve a distinguere proprietario e reception nella lettura del lavoro svolto.',
                        ],
                    ],
                    [
                        'title' => 'Consegne',
                        'items' => [
                            'La nuova consegna si usa per lasciare istruzioni, promemoria o informazioni operative al turno successivo.',
                            'Il titolo aiuta a capire subito l argomento della consegna.',
                            'L importanza serve a far capire se la consegna e urgente, normale o bassa.',
                            'Una consegna puo restare aperta finche la persona successiva non la legge, la continua o la chiude.',
                        ],
                    ],
                    [
                        'title' => 'Entrate / uscite e attivita',
                        'items' => [
                            'Entrate / uscite mostra quando una persona e entrata e quando e uscita dal programma.',
                            'Attivita mostra dove la persona ha lavorato, per esempio Clienti, Schedine, Questura o Tabella A Emilia-Romagna.',
                            'Questo storico serve per capire chi ha creato, modificato o controllato una parte del lavoro.',
                        ],
                    ],
                ],
            ],
        ];

        $troubleshooting = [
            [
                'title' => 'Non riesco a entrare nel programma',
                'problem' => 'Il sistema rifiuta accesso o password.',
                'solution' => [
                    'Controlla di usare il nome di accesso corretto della struttura.',
                    'Controlla di usare la password personale della persona che sta entrando.',
                    'Se la password non funziona, il proprietario può cambiarla in Utenti e consegne.',
                ],
                'keywords' => 'login password accesso utente reception proprietario',
            ],
            [
                'title' => 'Non riesco a creare un utente',
                'problem' => 'Il nuovo utente non viene salvato.',
                'solution' => [
                    'Compila nome e cognome, nome da vedere in alto e password personale.',
                    'La password deve avere almeno 8 caratteri.',
                    'Se hai inserito una email, deve essere valida e non già usata da un altro utente.',
                ],
                'keywords' => 'utente creare password email valida',
            ],
            [
                'title' => 'Una schedina non si salva',
                'problem' => 'Il sistema segnala dati obbligatori mancanti.',
                'solution' => [
                    'Controlla il tab dove il sistema ti porta automaticamente.',
                    'Completa i dati obbligatori dell ospite principale.',
                    'Se hai aggiunto componenti, completa tutti i dati del componente oppure rimuovi il componente vuoto.',
                ],
                'keywords' => 'schedina non salva obbligatori componenti',
            ],
            [
                'title' => 'Questura non genera il file',
                'problem' => 'Il periodo risulta non esportabile.',
                'solution' => [
                    'Controlla quali schedine sono segnate come da correggere.',
                    'Apri la schedina e completa i dati obbligatori del documento e della provenienza.',
                    'Poi torna in Questura e aggiorna l elenco.',
                ],
                'keywords' => 'questura txt non esportabile correggere',
            ],
            [
                'title' => 'Tabella A Emilia-Romagna non genera l XML',
                'problem' => 'Il controllo segnala dati giornalieri o mensili mancanti.',
                'solution' => [
                    'Controlla il periodo scelto.',
                    'Verifica il riepilogo giornaliero del mese.',
                    'Completa i dati della struttura e i movimenti richiesti, poi aggiorna il controllo.',
                ],
                'keywords' => 'tavola a xml controllo giornaliero istat ross1000',
            ],
            [
                'title' => 'Non trovo una consegna importante',
                'problem' => 'La consegna sembra sparita o troppo vecchia.',
                'solution' => [
                    'Apri la scheda Consegne registrate.',
                    'Le consegne aperte stanno sopra, poi quelle viste, poi quelle chiuse.',
                    'Usa il pulsante Apri per leggere il testo completo e continuare la conversazione.',
                ],
                'keywords' => 'consegna aperta vista chiusa apri risposta',
            ],
            [
                'title' => 'Ho bisogno dell amministratore del software',
                'problem' => 'Il manuale non basta oppure c e un problema che deve vedere direttamente il supporto.',
                'solution' => [
                    'Apri Centro di supporto > Supporto online.',
                    'Crea un nuovo ticket con titolo chiaro, modulo coinvolto e descrizione completa del problema.',
                    'Segui le risposte dentro il ticket e chiudi il caso quando il problema e risolto.',
                ],
                'keywords' => 'supporto online ticket admin software problema',
            ],
        ];

        $adminTopics = [
            [
                'slug' => 'superadmin',
                'title' => 'Superadmin',
                'icon' => 'ri-shield-star-line',
                'keywords' => 'superadmin qa sessione accesso tenancy pagamenti licenze articoli catalogo impersonazione selezione struttura 403 dashboard',
                'summary' => 'Spiega cosa governa il superadmin, quali parti del sistema sono riservate a lui e come intervenire quando qualcosa non torna nella catena amministrativa.',
                'when' => 'Usa questa guida quando devi capire se una funzione sta solo nel superadmin oppure anche nell admin, oppure quando devi correggere relazioni tra amministratori, proprietari e strutture.',
                'result' => 'Alla fine sai quali settori sono esclusivi del superadmin, quali puo delegare e come riparare gli errori piu comuni della struttura organizzativa.',
                'details' => [
                    'Il superadmin e il livello piu alto del sistema. Può vedere tutto, entrare in tutti i pannelli amministrativi e correggere la struttura generale del software.',
                    'Nel superadmin vivono le funzioni che governano gli amministratori del sistema, il catalogo articoli, l impersonazione, i pagamenti e la visione completa di proprietari e strutture.',
                    'Se ti chiedi se un settore sta solo nel superadmin o anche nell admin, la regola pratica e questa: tutto cio che riguarda gli amministratori resta al superadmin; proprietari e strutture possono poi essere gestiti anche dall admin secondo il perimetro assegnato.',
                    'Il pannello QA e un cruscotto tecnico di controllo. Non serve al lavoro quotidiano della struttura, ma a verificare sessione, permessi attesi e separazione dei dati.',
                ],
                'quick_links' => [
                    ['title' => 'Dashboard QA', 'route' => 'qa.index'],
                    ['title' => 'Seleziona struttura', 'route' => 'strutture.seleziona.index'],
                    ['title' => 'Articoli', 'route' => 'superadmin.articoli.index'],
                    ['title' => 'Proforme', 'route' => 'superadmin.proforme.index'],
                    ['title' => 'Pagamenti e licenze', 'route' => 'superadmin.pagamenti.index'],
                    ['title' => 'Impersonazione', 'route' => 'superadmin.impersonazione.index'],
                    ['title' => 'Amministratori', 'route' => 'superadmin.amministratori.index'],
                    ['title' => 'Proprietari', 'route' => 'superadmin.proprietari.index'],
                    ['title' => 'Strutture', 'route' => 'superadmin.strutture.index'],
                ],
                'field_groups' => [
                    [
                        'title' => 'Cosa amministra il superadmin',
                        'items' => [
                            'Amministratori: crea, modifica e disattiva gli admin del sistema.',
                            'Proprietari: vede tutti i proprietari, li crea, li modifica e puo assegnare o cambiare l admin responsabile.',
                            'Strutture: vede tutte le strutture, le crea, le modifica e ne controlla servizio, piano, stato pagamento, proprietario e licenze collegate.',
                            'Articoli: gestisce il catalogo commerciale del sistema, cioe i prodotti che verranno usati in licenze e fatturazione.',
                            'Pagamenti e licenze: assegna licenze a proprietari e strutture, aggiorna scadenze e puo stampare la singola licenza.',
                            'Impersonazione: entra temporaneamente come un altro utente per verificare problemi o spiegare un funzionamento.',
                        ],
                    ],
                    [
                        'title' => 'QA Dashboard: cosa significa ogni voce',
                        'items' => [
                            'Sessione: mostra l utente loggato in quel momento. ID e il numero interno del record utente, email e il login, ruolo indica il perimetro con cui il sistema sta lavorando.',
                            'Session struttura_corrente_id: e l id della struttura memorizzato nella sessione del browser. Se e vuoto, non hai ancora scelto una struttura operativa.',
                            'StrutturaCorrente::getId(): e il valore reale che il software sta usando come struttura corrente in quel momento.',
                            'Impersonator_id e impersonated_id servono a capire se stai lavorando come un altro utente. Se sono valorizzati, sei in impersonazione.',
                            'Verifica servizio struttura controlla attiva, scadenza servizio, piano, stato pagamento e servizioAttivo(). Se servizioAttivo e No, alcuni moduli operativi possono bloccarsi o chiedere correzione.',
                            'Accesso mostra una matrice teorica: OK significa che quella rotta deve aprirsi per quel ruolo, 403 significa che il blocco di accesso e corretto e voluto.',
                            'Se una riga attesa come OK restituisce 403, vuol dire che ruolo o middleware stanno bloccando troppo. Se una riga attesa come 403 apre comunque, il sistema sta concedendo troppo.',
                            'Tenancy significa isolamento dei dati per struttura. Il pannello controlla i conteggi per struttura_id e segnala anche eventuali record legacy con struttura_id NULL.',
                        ],
                    ],
                    [
                        'title' => 'Come scegliere la struttura dentro il superadmin',
                        'items' => [
                            'Il superadmin puo lavorare come supervisore puro oppure scegliere una struttura operativa quando deve entrare nei moduli che dipendono dalla struttura corrente.',
                            'Per scegliere la struttura usa il pannello Seleziona struttura. Quel pannello salva l id scelto nella sessione come struttura_corrente_id.',
                            'Dopo la selezione, i moduli operativi lavorano su quella struttura. Se non scegli nulla, alcuni flussi possono chiederti di selezionarla prima oppure usare la prima disponibile.',
                            'L admin puo fare la stessa cosa, ma solo tra le strutture del suo perimetro. Il proprietario vede solo le proprie strutture.',
                        ],
                    ],
                    [
                        'title' => 'Pagamenti e licenze: cosa fa oggi davvero',
                        'items' => [
                            'Il pannello oggi e diviso in due blocchi: Licenze assegnate e Servizi struttura.',
                            'Licenze assegnate contiene due sotto-sezioni: elenco licenze e nuova licenza. Da qui puoi assegnare una licenza a un proprietario o a una struttura, aggiornare stato pagamento, prezzo, scadenza e stampare la licenza.',
                            'Ogni licenza ha un numero univoco e un codice tracking. Questo serve per riconoscerla, stamparla e seguirla nel tempo.',
                            'Servizi struttura resta il quadro operativo della struttura: attiva o disattiva il servizio, gestisce scadenza, piano e stato pagamento.',
                            'Le proforme vivono nello stesso circuito amministrativo: da li puoi leggere documenti aperti, stamparli, chiuderli e segnare il pagamento con data e numero fattura.',
                        ],
                    ],
                    [
                        'title' => 'Articoli: cosa sono e perche stanno separati',
                        'items' => [
                            'Articoli e il catalogo base del sistema: qui definisci i prodotti che userai poi in licenze e fatturazione.',
                            'Ogni articolo puo avere nome, codice, accesso chiave, prezzo base, stato attivo e relazione padre/figlio.',
                            'Tenere gli articoli separati da Pagamenti e licenze evita di confondere il prodotto commerciale con la singola assegnazione o il singolo pagamento.',
                            'Quando modifichi un articolo, stai cambiando il modello commerciale. Quando modifichi una licenza, stai lavorando su un assegnazione concreta a proprietario o struttura.',
                        ],
                    ],
                    [
                        'title' => 'Come funziona la scheda struttura con licenze',
                        'items' => [
                            'La struttura legge gli stessi dati del backoffice: cio che il superadmin o l admin assegnano come licenza principale alla struttura viene riflesso anche nel pannello struttura.',
                            'Dentro Modifica struttura trovi i dati base della struttura, poi il riquadro Relazione struttura e infine Licenze e pagamenti della struttura.',
                            'Relazione struttura mostra proprietario, amministratore di riferimento, stato pagamento, piano e scadenza del servizio.',
                            'Licenze e pagamenti della struttura mostra tutte le licenze collegate a quella struttura, con tracking, prezzo, stato, scadenza e stampa.',
                            'Dal blocco Assegna nuova licenza alla struttura puoi scegliere l articolo, vedere prezzo e accesso caricati dal catalogo, impostare stato pagamento e date e salvare restando dentro la stessa struttura.',
                            'Se una struttura non ha ancora proprietario, la scheda non si blocca: puoi salvarla e completare la relazione in un secondo momento.',
                        ],
                    ],
                    [
                        'title' => 'Impersonazione: come funziona e a cosa serve',
                        'items' => [
                            'Impersonazione permette al superadmin di entrare temporaneamente come un altro utente senza conoscere la sua password.',
                            'Serve per riprodurre problemi veri: vedere esattamente cosa vede un admin, un proprietario o un utente struttura e capire perche manca un pulsante o compare un blocco.',
                            'Quando inizi, il sistema salva impersonator_id, impersonated_id e un log con data, IP e user agent. Poi esegue il login come utente target.',
                            'Se il target ha una struttura_id, quella struttura viene caricata subito come struttura corrente. Se non ce l ha, dovrai selezionare una struttura a mano se vuoi entrare nei moduli operativi.',
                            'Quando hai finito, usa sempre Esci impersonazione per tornare al superadmin originale e pulire lo stato di lavoro.',
                        ],
                    ],
                    [
                        'title' => 'Come capire se un settore e solo superadmin',
                        'items' => [
                            'Se il settore parla di amministratori del sistema, e solo superadmin.',
                            'Se il settore parla di proprietari o strutture, puo esistere sia nel superadmin sia nell admin.',
                            'Se il settore riguarda il lavoro operativo di una singola struttura, allora si scende a proprietario o struttura_user.',
                        ],
                    ],
                    [
                        'title' => 'Errori tipici e come risolverli',
                        'items' => [
                            'Un admin non vede nessun proprietario: controlla che il proprietario abbia `admin_id` assegnato correttamente.',
                            'Un proprietario non vede strutture: controlla che le strutture abbiano `proprietario_id` valorizzato.',
                            'Una struttura non appare nel perimetro previsto: verifica la catena completa `admin -> proprietario -> struttura`.',
                            'Un ruolo alto entra ma non vede dati: controlla se esiste solo il profilo utente ma mancano le relazioni amministrative di base.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'admin',
                'title' => 'Admin',
                'icon' => 'ri-admin-line',
                'keywords' => 'admin proprietari strutture selezione struttura perimetro assegnazioni pagamenti licenze catalogo struttura',
                'summary' => 'Spiega cosa puo fare l admin, quali aree condivide con il superadmin e come gestisce proprietari e strutture senza toccare i livelli superiori.',
                'when' => 'Usa questa guida quando devi amministrare il perimetro operativo assegnato all admin o capire fino a dove arrivano i suoi permessi.',
                'result' => 'Alla fine sai cosa puo fare davvero l admin, dove si ferma e come correggere i problemi piu frequenti di assegnazione.',
                'details' => [
                    'L admin non governa gli amministratori del sistema: quello resta compito del superadmin.',
                    'L admin puo invece gestire i proprietari che gli sono assegnati e le strutture che dipendono da quei proprietari.',
                    'Per questo motivo alcune aree esistono sia nel superadmin sia nell admin, ma l admin le vede solo entro il suo perimetro.',
                ],
                'quick_links' => [
                    ['title' => 'Proprietari', 'route' => 'admin.proprietari.index'],
                    ['title' => 'Strutture', 'route' => 'admin.strutture.index'],
                    ['title' => 'Proforme', 'route' => 'admin.proforme.index'],
                    ['title' => 'Pagamenti e licenze', 'route' => 'admin.pagamenti.index'],
                    ['title' => 'Seleziona struttura', 'route' => 'strutture.seleziona.index'],
                ],
                'field_groups' => [
                    [
                        'title' => 'Cosa amministra l admin',
                        'items' => [
                            'Proprietari: crea, modifica e disattiva solo i proprietari che rientrano sotto il suo controllo.',
                            'Strutture: crea, modifica e gestisce il servizio delle strutture appartenenti ai suoi proprietari.',
                            'Pagamenti e licenze: puo leggere il catalogo centrale e assegnare o aggiornare licenze solo per proprietari e strutture del proprio perimetro.',
                            'Relazioni: mantiene ordinata la catena tra proprietario e strutture, senza intervenire sul livello superadmin.',
                        ],
                    ],
                    [
                        'title' => 'Cosa non fa l admin',
                        'items' => [
                            'Non crea amministratori del sistema.',
                            'Non entra nel pannello superadmin.',
                            'Non governa proprietari assegnati ad altri admin.',
                        ],
                    ],
                    [
                        'title' => 'Come lavora l admin tra piu strutture',
                        'items' => [
                            'L admin puo scegliere una struttura corrente dal selettore generale quando deve entrare nei moduli operativi di una struttura precisa.',
                            'Quella scelta vale solo per le strutture appartenenti ai proprietari assegnati all admin.',
                            'Se non ci sono ancora relazioni, oggi l admin puo comunque entrare. Questo serve per configurare il perimetro da zero senza ricevere blocchi iniziali.',
                        ],
                    ],
                    [
                        'title' => 'Come lavora l admin con licenze e pagamenti',
                        'items' => [
                            'L admin entra in Pagamenti e licenze solo sul proprio perimetro: vede i suoi proprietari, le sue strutture e le licenze collegate.',
                            'Il catalogo articoli per l admin e in sola lettura: puo usarlo per assegnare licenze ma non per cambiare i prodotti globali del sistema.',
                            'Anche l admin puo aprire la scheda di una struttura e da li vedere proprietario, licenze attive, stato pagamento e aggiungere una nuova licenza alla struttura.',
                            'Quando aggiorna o assegna una licenza dalla scheda struttura, il sistema lo riporta alla stessa struttura per non spezzare il flusso di lavoro.',
                            'Da Proforme vede il quadro documentale del proprio perimetro, apre il documento, lo stampa e puo segnare una proforma come pagata con numero fattura e data pagamento.',
                        ],
                    ],
                    [
                        'title' => 'Errori tipici e come risolverli',
                        'items' => [
                            'Vedi zero proprietari: controlla che il proprietario abbia `admin_id` uguale all id dell admin corrente.',
                            'Vedi zero strutture: controlla prima che esista un proprietario legato all admin, poi che la struttura sia collegata a quel proprietario.',
                            'Non vedi una licenza in struttura: controlla che la licenza sia stata assegnata con `struttura_id` valorizzato oppure alla struttura corretta del tuo perimetro.',
                            'Puoi entrare ma non hai ancora dati: non e per forza un errore. L admin ora puo accedere anche in configurazione iniziale e creare da zero il proprio perimetro.',
                        ],
                    ],
                ],
            ],
        ];

        $managementTopics = [
            [
                'slug' => 'proprietario',
                'title' => 'Proprietario',
                'icon' => 'ri-admin-line',
                'summary' => 'Spiega cosa configura il proprietario, quali responsabilita ha e come controlla utenti, accessi, consegne e attivita.',
                'when' => 'Usa questa guida quando devi capire come il proprietario prepara il lavoro della struttura e controlla la parte sensibile del programma.',
                'result' => 'Alla fine sai cosa deve fare il proprietario, come si leggono i dati della gestione e quali decisioni spettano a lui.',
                'details' => [
                    'Il proprietario e la persona che prepara il programma per tutti gli altri utenti della struttura.',
                    'Da questa area controlla utenti autorizzati, password, profili, consegne, storico entrate / uscite e attivita svolte.',
                    'Il proprietario non deve fare per forza tutto il lavoro operativo di reception, ma deve impostare bene chi puo entrare e come si passa il turno.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Cosa fa il proprietario',
                        'items' => [
                            'Crea le persone che possono entrare nel programma.',
                            'Decide chi e attivo e chi non deve piu entrare.',
                            'Controlla il passaggio di turno con le consegne.',
                            'Legge entrate / uscite e attivita per capire chi ha lavorato e dove ha lavorato.',
                        ],
                    ],
                    [
                        'title' => 'Cosa controlla ogni giorno',
                        'items' => [
                            'Se le persone autorizzate sono corrette e aggiornate.',
                            'Se ci sono consegne aperte da leggere o chiudere.',
                            'Se il lavoro della reception e stato registrato nelle aree corrette del programma.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'reception',
                'title' => 'Reception',
                'icon' => 'ri-user-voice-line',
                'summary' => 'Spiega come lavora la reception: accesso, lettura consegne, uso del profilo e continuita del turno.',
                'when' => 'Usa questa guida quando devi formare una persona di reception o spiegare come entra e come lascia il lavoro al turno successivo.',
                'result' => 'Alla fine la reception sa come entrare, cosa leggere per prima, come lasciare consegne e come farsi riconoscere dal sistema.',
                'details' => [
                    'La reception entra sempre con il nome di accesso comune della struttura e con la sua password personale.',
                    'Il sistema riconosce la persona dal modo in cui entra e registra il suo lavoro nelle attivita.',
                    'Prima di iniziare conviene leggere le consegne aperte e poi lavorare su clienti, schedine e movimenti.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Flusso del turno',
                        'items' => [
                            'Entra nel programma con la tua password personale.',
                            'Controlla se ci sono consegne aperte del turno precedente.',
                            'Lavora normalmente nel programma.',
                            'Prima di uscire, lascia una consegna se il turno successivo deve sapere qualcosa.',
                        ],
                    ],
                    [
                        'title' => 'Cosa vede in alto nel programma',
                        'items' => [
                            'Il nome da vedere in alto identifica la persona che sta lavorando.',
                            'Il ruolo aiuta a capire subito se la persona e reception o proprietario.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'utenti',
                'title' => 'Utenti',
                'icon' => 'ri-team-line',
                'summary' => 'Spiega come creare e amministrare le persone autorizzate che possono entrare nel programma della struttura.',
                'when' => 'Usa questa guida quando devi aggiungere una nuova persona, disattivarla o capire quali dati vanno compilati.',
                'result' => 'Alla fine sai creare correttamente un utente e capire come funziona il nome di accesso comune della struttura.',
                'details' => [
                    'Gli utenti sono le persone reali che possono entrare nel programma della struttura.',
                    'Non si crea un nome di accesso diverso per ogni persona: il nome di accesso della struttura resta uguale per tutti.',
                    'Quello che cambia e la password personale, che identifica chi sta lavorando.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Come si crea un utente',
                        'items' => [
                            'Apri Utenti e consegne e vai nella scheda Nuovo utente.',
                            'Compila nome e cognome della persona.',
                            'Compila il nome da vedere in alto: e quello che si vedra nella topbar.',
                            'Compila telefono ed email di emergenza se vuoi rendere la persona contattabile fuori servizio.',
                            'Scegli il ruolo tra Proprietario e Reception.',
                            'Imposta la password personale.',
                            'Lascia la persona attiva se deve poter entrare subito nel programma.',
                        ],
                    ],
                    [
                        'title' => 'Come si legge l elenco utenti',
                        'items' => [
                            'Persona: indica chi e la persona registrata nel sistema.',
                            'Ruolo: distingue proprietario e reception.',
                            'Telefono: serve come contatto rapido.',
                            'Stato: indica se la persona puo ancora entrare oppure no.',
                            'Azioni: permettono di modificare i dati o cambiare la password.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'profilo',
                'title' => 'Profilo',
                'icon' => 'ri-account-circle-line',
                'summary' => 'Spiega come si compila il profilo personale e a cosa servono i dati mostrati in alto nel sistema.',
                'when' => 'Usa questa guida quando vuoi aggiornare il nome visualizzato, i recapiti o capire come il sistema mostra la persona che sta lavorando.',
                'result' => 'Alla fine il profilo resta chiaro, riconoscibile e utile anche per contattare la persona se serve.',
                'details' => [
                    'Il profilo serve a far vedere chi sta lavorando in quel momento.',
                    'I dati del profilo non cambiano il nome di accesso comune della struttura.',
                    'Telefono ed email di contatto sono utili per i contatti interni o in emergenza.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Dati del profilo',
                        'items' => [
                            'Nome e cognome: identificano formalmente la persona.',
                            'Nome da vedere in alto: e il nome breve mostrato nella topbar del programma.',
                            'Telefono: serve come contatto rapido.',
                            'Email di contatto: serve come contatto secondario o di emergenza.',
                            'Ruolo: distingue la funzione della persona ma non si cambia liberamente se non secondo la gestione del proprietario.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'consegne',
                'title' => 'Consegne',
                'icon' => 'ri-mail-open-line',
                'summary' => 'Spiega come usare il libro consegne per il passaggio di turno tra una persona e l altra.',
                'when' => 'Usa questa guida quando devi lasciare una nota al turno successivo, leggere una consegna ricevuta o continuare una conversazione.',
                'result' => 'Alla fine il passaggio di turno resta ordinato, leggibile e non si perdono le informazioni importanti.',
                'details' => [
                    'Una consegna e un messaggio operativo lasciato da una persona a un altra o al turno successivo.',
                    'Le consegne possono essere aperte, viste o chiuse.',
                    'Una consegna lunga si apre in finestra dedicata e puo essere continuata con una risposta.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Come si scrive una consegna',
                        'items' => [
                            'Titolo: riassume in poche parole l argomento della consegna.',
                            'Per chi: permette di lasciarla a una persona precisa oppure al turno successivo in generale.',
                            'Importanza: aiuta a capire se la consegna e urgente, normale o bassa.',
                            'Messaggio: contiene tutto il testo operativo da passare a chi entra dopo.',
                        ],
                    ],
                    [
                        'title' => 'Come si leggono gli stati',
                        'items' => [
                            'Da vedere: la consegna e ancora da leggere o da gestire.',
                            'Vista: la consegna e stata letta ma puo essere ancora utile o aperta.',
                            'Chiusa: la consegna e conclusa e non richiede piu azioni.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'entrate-uscite',
                'title' => 'Entrate e uscite',
                'icon' => 'ri-login-box-line',
                'summary' => 'Spiega come il sistema registra l entrata nel programma e l uscita dal programma per ogni persona.',
                'when' => 'Usa questa guida quando vuoi capire quando una persona e entrata, quando e uscita e come si legge questo storico.',
                'result' => 'Alla fine sai leggere correttamente i momenti di accesso e uscita dal programma.',
                'details' => [
                    'L entrata si registra quando la persona entra nel programma con la propria password personale.',
                    'L uscita si registra quando la persona esce dal programma.',
                    'Questo storico aiuta a capire chi era presente nel sistema in un certo momento.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Come si legge questa sezione',
                        'items' => [
                            'Persona: indica chi e entrato o uscito.',
                            'Entrata: indica il momento in cui la persona ha iniziato il turno nel programma.',
                            'Uscita: indica il momento in cui la persona ha terminato il lavoro nel programma.',
                            'Stato: aiuta a capire se la sessione e ancora aperta o gia conclusa.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'attivita',
                'title' => 'Attivita',
                'icon' => 'ri-file-history-line',
                'summary' => 'Spiega come leggere lo storico del lavoro svolto da ciascuna persona nelle varie parti del programma.',
                'when' => 'Usa questa guida quando vuoi ricostruire chi ha lavorato su clienti, schedine, invii telematici o altre aree del sistema.',
                'result' => 'Alla fine capisci chi ha fatto un azione, in quale parte del programma e in quale momento.',
                'details' => [
                    'Le attivita non mostrano solo una data: aiutano a capire in che zona del programma ha lavorato la persona.',
                    'Questo storico e utile per ricostruire il lavoro svolto durante il turno o in una giornata precisa.',
                ],
                'field_groups' => [
                    [
                        'title' => 'Come si legge questa sezione',
                        'items' => [
                            'Data e ora: indicano il momento in cui e stata registrata l azione.',
                            'Persona: indica chi stava lavorando.',
                            'Parte del programma: dice dove la persona ha lavorato, per esempio Clienti, Schedine o Invio telematico.',
                            'Azione: spiega in modo sintetico cosa e stato fatto in quella parte del programma.',
                        ],
                    ],
                ],
            ],
        ];

        return [
            'guide' => $guide,
            'modules' => $modules,
            'faqs' => $faqs,
            'personas' => $personas,
            'troubleshooting' => $troubleshooting,
            'managementTopics' => $managementTopics,
            'adminTopics' => $adminTopics,
        ];
    }

    private function orderGuide(array $guide): array
    {
        $order = [
            'Come si entra nel programma',
            'Come si configura la struttura',
            'Come si lavora con clienti e schedine',
            'Come si gestiscono gli invii telematici',
            'Come si chiede supporto',
            'Come si chiude il turno',
        ];

        return collect($guide)->sortBy(fn (array $item) => array_search($item['title'], $order, true))->values()->all();
    }

    private function orderModules(array $modules): array
    {
        $order = [
            'struttura',
            'configurazioni',
            'clienti',
            'schedine',
            'arrivi',
            'web-checkin',
            'questura',
            'tavola-a',
            'presenze',
            'tassa-soggiorno',
            'notifiche',
            'utenti-consegne',
            'supporto-online',
            'cestino',
        ];

        return collect($modules)->sortBy(fn (array $item) => array_search($item['slug'] ?? '', $order, true))->values()->all();
    }

    private function orderPersonas(array $personas): array
    {
        $order = ['Per Proprietario', 'Per Reception'];

        return collect($personas)->sortBy(fn (array $item) => array_search($item['title'], $order, true))->values()->all();
    }

    private function orderManagementTopics(array $topics): array
    {
        $order = [
            'proprietario',
            'reception',
            'utenti',
            'profilo',
            'consegne',
            'entrate-uscite',
            'attivita',
        ];

        return collect($topics)->sortBy(fn (array $item) => array_search($item['slug'] ?? '', $order, true))->values()->all();
    }

    private function orderAdminTopics(array $topics): array
    {
        $order = [
            'superadmin',
            'admin',
        ];

        return collect($topics)->sortBy(fn (array $item) => array_search($item['slug'] ?? '', $order, true))->values()->all();
    }
}
