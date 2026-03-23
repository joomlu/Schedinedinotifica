<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Titolo;

class TitoloSeeder extends Seeder
{
    public function run(): void
    {
        $titoli = [
            // Titoli generali
            ['nome' => 'Sig.', 'descrizione' => 'Signore'],
            ['nome' => 'Sig.ra', 'descrizione' => 'Signora'],
            ['nome' => 'Sig.na', 'descrizione' => 'Signorina'],
            ['nome' => 'Sig.re', 'descrizione' => 'Signore'],
            ['nome' => 'Gent.mo', 'descrizione' => 'Gentilissimo'],
            ['nome' => 'Gent.ma', 'descrizione' => 'Gentilissima'],
            ['nome' => 'Gent.mi', 'descrizione' => 'Gentilissimi'],
            ['nome' => 'Gent.me', 'descrizione' => 'Gentilissime'],
            ['nome' => 'Gent. Sig.', 'descrizione' => 'Gentile Signore'],
            ['nome' => 'Gent. Sig.ra', 'descrizione' => 'Gentile Signora'],
            ['nome' => 'Spett.le', 'descrizione' => 'Spettabile'],
            ['nome' => 'Egr.', 'descrizione' => 'Egregio'],
            ['nome' => 'Ill.mo', 'descrizione' => 'Illustrissimo'],
            ['nome' => 'Ch.mo', 'descrizione' => 'Chiarissimo'],

            // Titoli accademici
            ['nome' => 'Dott.', 'descrizione' => 'Dottore'],
            ['nome' => 'Dott.ssa', 'descrizione' => 'Dottoressa'],
            ['nome' => 'Dr.', 'descrizione' => 'Doctor'],
            ['nome' => 'PhD', 'descrizione' => 'Dottore di ricerca'],
            ['nome' => 'Prof.', 'descrizione' => 'Professore'],
            ['nome' => 'Prof.ssa', 'descrizione' => 'Professoressa'],
            ['nome' => 'Ric.', 'descrizione' => 'Ricercatore'],
            ['nome' => 'Ric.ssa', 'descrizione' => 'Ricercatrice'],

            // Professioni
            ['nome' => 'Avv.', 'descrizione' => 'Avvocato'],
            ['nome' => 'Ing.', 'descrizione' => 'Ingegnere'],
            ['nome' => 'Arch.', 'descrizione' => 'Architetto'],
            ['nome' => 'Geom.', 'descrizione' => 'Geometra'],
            ['nome' => 'Per. Ind.', 'descrizione' => 'Perito Industriale'],
            ['nome' => 'Per. Agr.', 'descrizione' => 'Perito Agrario'],
            ['nome' => 'Rag.', 'descrizione' => 'Ragioniere'],
            ['nome' => 'Comm.', 'descrizione' => 'Commendatore'],
            ['nome' => 'Cons.', 'descrizione' => 'Consulente'],
            ['nome' => 'Dir.', 'descrizione' => 'Direttore'],
            ['nome' => 'Resp.', 'descrizione' => 'Responsabile'],
            ['nome' => 'Amm.', 'descrizione' => 'Amministratore'],

            // Istituzionali
            ['nome' => 'On.', 'descrizione' => 'Onorevole'],
            ['nome' => 'Sen.', 'descrizione' => 'Senatore'],
            ['nome' => 'Min.', 'descrizione' => 'Ministro'],
            ['nome' => 'Pref.', 'descrizione' => 'Prefetto'],
            ['nome' => 'Quest.', 'descrizione' => 'Questore'],
            ['nome' => 'Sind.', 'descrizione' => 'Sindaco'],
            ['nome' => 'Cons.re', 'descrizione' => 'Consigliere'],

            // Militari
            ['nome' => 'Gen.', 'descrizione' => 'Generale'],
            ['nome' => 'Col.', 'descrizione' => 'Colonnello'],
            ['nome' => 'Ten.', 'descrizione' => 'Tenente'],
            ['nome' => 'Cap.', 'descrizione' => 'Capitano'],
            ['nome' => 'Mag.', 'descrizione' => 'Maggiore'],
            ['nome' => 'Serg.', 'descrizione' => 'Sergente'],

            // Religiosi
            ['nome' => 'Don', 'descrizione' => 'Don'],
            ['nome' => 'Padre', 'descrizione' => 'Padre'],
            ['nome' => 'Fra', 'descrizione' => 'Frate'],
            ['nome' => 'Suor', 'descrizione' => 'Suora'],
            ['nome' => 'Mons.', 'descrizione' => 'Monsignore'],
            ['nome' => 'Rev.', 'descrizione' => 'Reverendo'],
            ['nome' => 'Rev.mo', 'descrizione' => 'Reverendissimo'],
            ['nome' => 'Past.', 'descrizione' => 'Pastore'],
            ['nome' => 'Madre', 'descrizione' => 'Madre'],
            ['nome' => 'Emin.', 'descrizione' => 'Eminenza'],

            // Nobiliari
            ['nome' => 'Principe', 'descrizione' => 'Principe'],
            ['nome' => 'Principessa', 'descrizione' => 'Principessa'],
            ['nome' => 'Conte', 'descrizione' => 'Conte'],
            ['nome' => 'Contessa', 'descrizione' => 'Contessa'],
            ['nome' => 'Marchese', 'descrizione' => 'Marchese'],
            ['nome' => 'Marchesa', 'descrizione' => 'Marchesa'],
            ['nome' => 'Duca', 'descrizione' => 'Duca'],
            ['nome' => 'Duchessa', 'descrizione' => 'Duchessa'],
            ['nome' => 'Barone', 'descrizione' => 'Barone'],
            ['nome' => 'Baronessa', 'descrizione' => 'Baronessa'],
            ['nome' => 'Baronetto', 'descrizione' => 'Baronetto'],
            ['nome' => 'Dama', 'descrizione' => 'Dama'],
            ['nome' => 'Sir', 'descrizione' => 'Sir'],

            // Onorifici
            ['nome' => 'Cav.', 'descrizione' => 'Cavaliere'],
            ['nome' => 'Cav. Uff.', 'descrizione' => 'Cavaliere Ufficiale'],
            ['nome' => 'Grand\'Uff.', 'descrizione' => 'Grand\'Ufficiale'],
            ['nome' => 'Cav. Lav.', 'descrizione' => 'Cavaliere del Lavoro'],

            // Altro
            ['nome' => 'Sig. e Sig.ra', 'descrizione' => 'Signore e Signora'],
            ['nome' => 'Fam.', 'descrizione' => 'Famiglia'],
            ['nome' => 'Ospite', 'descrizione' => 'Ospite'],
            ['nome' => 'Cliente', 'descrizione' => 'Cliente'],
            ['nome' => 'Ditta', 'descrizione' => 'Ditta'],
            ['nome' => 'Soc.', 'descrizione' => 'Societa'],
        ];

        foreach ($titoli as $data) {
            Titolo::updateOrCreate(
                ['nome' => $data['nome']],
                [
                    'descrizione' => $data['descrizione'],
                    'attivo' => true,
                ]
            );
        }
    }
}
