<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gruppo;

class GruppiSeeder extends Seeder
{
    public function run(): void
    {
        $level1 = [
            ['nome' => 'Business', 'descrizione' => 'Segmento business travel'],
            ['nome' => 'Turismo', 'descrizione' => 'Tempo libero e vacanze'],
            ['nome' => 'Enogastronomia', 'descrizione' => 'Cibo e vino'],
            ['nome' => 'Sport', 'descrizione' => 'Viaggi legati allo sport'],
            ['nome' => 'Cultura', 'descrizione' => 'Musei, teatri, festival'],
            ['nome' => 'Famiglie', 'descrizione' => 'Target family'],
            ['nome' => 'Benessere', 'descrizione' => 'Spa, terme, relax'],
            ['nome' => 'Luxury', 'descrizione' => 'Esperienze di lusso'],
            ['nome' => 'Budget', 'descrizione' => 'Soluzioni economiche'],
            ['nome' => 'Avventura', 'descrizione' => 'Outdoor e adrenalina'],
            ['nome' => 'Educazione', 'descrizione' => 'Scambi e formazione'],
            ['nome' => 'Religione', 'descrizione' => 'Viaggi spirituali'],
            ['nome' => 'Natura', 'descrizione' => 'Natura e sostenibilita'],
            ['nome' => 'Eventi', 'descrizione' => 'Eventi e spettacoli'],
            ['nome' => 'Altro', 'descrizione' => 'Categorie varie'],
        ];

        $level2 = [
            ['nome' => 'Fiere', 'parent' => 'Business'],
            ['nome' => 'Meeting', 'parent' => 'Business'],
            ['nome' => 'Roadshow', 'parent' => 'Business'],
            ['nome' => 'Weekend', 'parent' => 'Turismo'],
            ['nome' => 'City break', 'parent' => 'Turismo'],
            ['nome' => 'Vacanze mare', 'parent' => 'Turismo'],
            ['nome' => 'Vacanze montagna', 'parent' => 'Turismo'],
            ['nome' => 'Degustazioni', 'parent' => 'Enogastronomia'],
            ['nome' => 'Ristoranti', 'parent' => 'Enogastronomia'],
            ['nome' => 'Street food', 'parent' => 'Enogastronomia'],
            ['nome' => 'Calcio', 'parent' => 'Sport'],
            ['nome' => 'Running', 'parent' => 'Sport'],
            ['nome' => 'Bike', 'parent' => 'Sport'],
            ['nome' => 'Sport invernali', 'parent' => 'Sport'],
            ['nome' => 'Musei', 'parent' => 'Cultura'],
            ['nome' => 'Teatri', 'parent' => 'Cultura'],
            ['nome' => 'Festival culturali', 'parent' => 'Cultura'],
            ['nome' => 'Parchi tematici', 'parent' => 'Famiglie'],
            ['nome' => 'Gite scolastiche', 'parent' => 'Famiglie'],
            ['nome' => 'Villaggi family', 'parent' => 'Famiglie'],
            ['nome' => 'Spa', 'parent' => 'Benessere'],
            ['nome' => 'Yoga retreat', 'parent' => 'Benessere'],
            ['nome' => 'Terme', 'parent' => 'Benessere'],
            ['nome' => 'Boutique stay', 'parent' => 'Luxury'],
            ['nome' => 'Yacht', 'parent' => 'Luxury'],
            ['nome' => 'Golf', 'parent' => 'Luxury'],
            ['nome' => 'Ostelli', 'parent' => 'Budget'],
            ['nome' => 'Campeggi', 'parent' => 'Budget'],
            ['nome' => 'Trekking', 'parent' => 'Avventura'],
            ['nome' => 'Climbing', 'parent' => 'Avventura'],
            ['nome' => 'Diving', 'parent' => 'Avventura'],
            ['nome' => 'Erasmus', 'parent' => 'Educazione'],
            ['nome' => 'Master', 'parent' => 'Educazione'],
            ['nome' => 'Summer school', 'parent' => 'Educazione'],
            ['nome' => 'Pellegrinaggi', 'parent' => 'Religione'],
            ['nome' => 'Ritiri', 'parent' => 'Religione'],
            ['nome' => 'Agriturismo', 'parent' => 'Natura'],
            ['nome' => 'Birdwatching', 'parent' => 'Natura'],
            ['nome' => 'Concerti', 'parent' => 'Eventi'],
            ['nome' => 'Matrimoni', 'parent' => 'Eventi'],
        ];

        $level3 = [
            ['nome' => 'Fiere internazionali', 'parent' => 'Fiere'],
            ['nome' => 'Expo settore', 'parent' => 'Fiere'],
            ['nome' => 'Board meeting', 'parent' => 'Meeting'],
            ['nome' => 'Team building', 'parent' => 'Meeting'],
            ['nome' => 'Roadshow Italia', 'parent' => 'Roadshow'],
            ['nome' => 'Roadshow Europa', 'parent' => 'Roadshow'],
            ['nome' => 'Weekend lungo', 'parent' => 'Weekend'],
            ['nome' => 'Short break', 'parent' => 'Weekend'],
            ['nome' => 'Arte e cultura', 'parent' => 'City break'],
            ['nome' => 'Shopping trip', 'parent' => 'City break'],
            ['nome' => 'Sardegna', 'parent' => 'Vacanze mare'],
            ['nome' => 'Puglia', 'parent' => 'Vacanze mare'],
            ['nome' => 'Dolomiti', 'parent' => 'Vacanze montagna'],
            ['nome' => 'Alpi', 'parent' => 'Vacanze montagna'],
            ['nome' => 'Vino', 'parent' => 'Degustazioni'],
            ['nome' => 'Olio', 'parent' => 'Degustazioni'],
            ['nome' => 'Stellati', 'parent' => 'Ristoranti'],
            ['nome' => 'Tipici', 'parent' => 'Ristoranti'],
            ['nome' => 'Food truck', 'parent' => 'Street food'],
            ['nome' => 'Mercati', 'parent' => 'Street food'],
            ['nome' => 'Trasferte tifosi', 'parent' => 'Calcio'],
            ['nome' => 'Camp estivi', 'parent' => 'Calcio'],
            ['nome' => 'Maratone', 'parent' => 'Running'],
            ['nome' => 'Mezze maratone', 'parent' => 'Running'],
            ['nome' => 'Ciclismo strada', 'parent' => 'Bike'],
            ['nome' => 'MTB', 'parent' => 'Bike'],
            ['nome' => 'Sci alpino', 'parent' => 'Sport invernali'],
            ['nome' => 'Snowboard', 'parent' => 'Sport invernali'],
            ['nome' => 'Mostre temporanee', 'parent' => 'Musei'],
            ['nome' => 'Collezioni permanenti', 'parent' => 'Musei'],
            ['nome' => 'Prosa', 'parent' => 'Teatri'],
            ['nome' => 'Musical', 'parent' => 'Teatri'],
            ['nome' => 'Letteratura', 'parent' => 'Festival culturali'],
            ['nome' => 'Cinema', 'parent' => 'Festival culturali'],
            ['nome' => 'Parchi avventura', 'parent' => 'Parchi tematici'],
            ['nome' => 'Parchi acquatici', 'parent' => 'Parchi tematici'],
            ['nome' => 'Scuole medie', 'parent' => 'Gite scolastiche'],
            ['nome' => 'Scuole superiori', 'parent' => 'Gite scolastiche'],
            ['nome' => 'Club animazione', 'parent' => 'Villaggi family'],
            ['nome' => 'Baby club', 'parent' => 'Villaggi family'],
            ['nome' => 'Day spa', 'parent' => 'Spa'],
            ['nome' => 'Resort spa', 'parent' => 'Spa'],
            ['nome' => 'Yoga mare', 'parent' => 'Yoga retreat'],
            ['nome' => 'Yoga montagna', 'parent' => 'Yoga retreat'],
            ['nome' => 'Week end terme', 'parent' => 'Terme'],
            ['nome' => 'Lungo soggiorno terme', 'parent' => 'Terme'],
            ['nome' => 'Hotel boutique', 'parent' => 'Boutique stay'],
            ['nome' => 'Heritage stay', 'parent' => 'Boutique stay'],
            ['nome' => 'Crociera charter', 'parent' => 'Yacht'],
            ['nome' => 'Weekend yacht', 'parent' => 'Yacht'],
            ['nome' => 'Tornei golf', 'parent' => 'Golf'],
            ['nome' => 'Clinic golf', 'parent' => 'Golf'],
            ['nome' => 'Studenti', 'parent' => 'Ostelli'],
            ['nome' => 'Backpacker', 'parent' => 'Ostelli'],
            ['nome' => 'Bungalow', 'parent' => 'Campeggi'],
            ['nome' => 'Tende', 'parent' => 'Campeggi'],
            ['nome' => 'Appennini', 'parent' => 'Trekking'],
            ['nome' => 'Isole', 'parent' => 'Trekking'],
            ['nome' => 'Falesie', 'parent' => 'Climbing'],
            ['nome' => 'Indoor', 'parent' => 'Climbing'],
            ['nome' => 'Barriera corallina', 'parent' => 'Diving'],
            ['nome' => 'Relitti', 'parent' => 'Diving'],
            ['nome' => 'Accoglienza Erasmus', 'parent' => 'Erasmus'],
            ['nome' => 'Welcome week', 'parent' => 'Erasmus'],
            ['nome' => 'Executive master', 'parent' => 'Master'],
            ['nome' => 'Full time master', 'parent' => 'Master'],
            ['nome' => 'Lingue', 'parent' => 'Summer school'],
            ['nome' => 'STEM', 'parent' => 'Summer school'],
            ['nome' => 'Cammini', 'parent' => 'Pellegrinaggi'],
            ['nome' => 'Santuari', 'parent' => 'Pellegrinaggi'],
            ['nome' => 'Ritiri spirituali', 'parent' => 'Ritiri'],
            ['nome' => 'Convegni religiosi', 'parent' => 'Ritiri'],
            ['nome' => 'Weekend contadino', 'parent' => 'Agriturismo'],
            ['nome' => 'Fattorie didattiche', 'parent' => 'Agriturismo'],
            ['nome' => 'Oasi naturali', 'parent' => 'Birdwatching'],
            ['nome' => 'Migrazioni', 'parent' => 'Birdwatching'],
            ['nome' => 'Tour musicali', 'parent' => 'Concerti'],
            ['nome' => 'Backstage crew', 'parent' => 'Concerti'],
            ['nome' => 'Destination wedding', 'parent' => 'Matrimoni'],
            ['nome' => 'Invitati matrimonio', 'parent' => 'Matrimoni'],
        ];

        $level1Ids = [];
        foreach ($level1 as $entry) {
            $record = Gruppo::updateOrCreate(
                [
                    'nome' => $entry['nome'],
                    'livello' => 1,
                    'parent_id' => null,
                ],
                [
                    'descrizione' => $entry['descrizione'] ?? null,
                    'tipo' => Gruppo::tipoFromLivello(1),
                    'livello' => 1,
                    'parent_id' => null,
                ]
            );
            $level1Ids[$entry['nome']] = $record->id;
        }

        $level2Ids = [];
        foreach ($level2 as $entry) {
            $parentId = $level1Ids[$entry['parent']] ?? null;
            if (! $parentId) {
                continue;
            }

            $record = Gruppo::updateOrCreate(
                [
                    'nome' => $entry['nome'],
                    'livello' => 2,
                    'parent_id' => $parentId,
                ],
                [
                    'descrizione' => $entry['descrizione'] ?? null,
                    'tipo' => Gruppo::tipoFromLivello(2),
                    'livello' => 2,
                    'parent_id' => $parentId,
                ]
            );
            $level2Ids[$entry['nome']] = $record->id;
        }

        foreach ($level3 as $entry) {
            $parentId = $level2Ids[$entry['parent']] ?? null;
            if (! $parentId) {
                continue;
            }

            Gruppo::updateOrCreate(
                [
                    'nome' => $entry['nome'],
                    'livello' => 3,
                    'parent_id' => $parentId,
                ],
                [
                    'descrizione' => $entry['descrizione'] ?? null,
                    'tipo' => Gruppo::tipoFromLivello(3),
                    'livello' => 3,
                    'parent_id' => $parentId,
                ]
            );
        }
    }
}
