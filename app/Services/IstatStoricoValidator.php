<?php

namespace App\Services;

use App\Models\IstatExport;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/** Uno scaricamento non prova l'accettazione: non inventa rettifiche remote. */
final class IstatStoricoValidator
{
    public function errors(int $strutturaId, Carbon $dal, Carbon $al, Collection $schedine): array
    {
        $exports = IstatExport::where('struttura_id', $strutturaId)
            ->whereDate('dal', '<=', $al->toDateString())->whereDate('al', '>=', $dal->toDateString())->latest('id')->get();
        $seen = [];
        $current = [];
        foreach ($schedine as $s) {
            $current['S'.$s->id] = ['arrivo' => $s->arrive, 'tipo' => (new IstatCodifiche())->tipo($s->relationship)];
            foreach ($s->componenti as $c) {
                $current['C'.$c->id] = ['arrivo' => $s->arrive, 'tipo' => (new IstatCodifiche())->tipo($c->relationship)];
            }
        }
        $errors = [];
        foreach ($exports as $export) {
            if (!str_starts_with($export->path, 'istat/struttura_'.$strutturaId.'/') || str_contains($export->path, '..') || !Storage::disk('local')->exists($export->path)) {
                $errors[] = 'Export storico #'.$export->id.': file non disponibile per verificare modifiche e cancellazioni.';
                continue;
            }
            try {
                $xml = Storage::disk('local')->get($export->path);
                $validator = new IstatXmlValidator();
                $validator->validate($xml);
                $doc = $validator->document($xml);
                $xp = new \DOMXPath($doc);
                foreach ($xp->query('/movimenti/movimento') as $movement) {
                    $day = $xp->evaluate('string(data)', $movement);
                    if (isset($seen[$day]) || $day < $dal->format('Ymd') || $day > $al->format('Ymd')) {
                        continue;
                    }
                    $seen[$day] = true;
                    foreach ($xp->query('arrivi/arrivo', $movement) as $guest) {
                        $id = $xp->evaluate('string(idswh)', $guest);
                        $type = $xp->evaluate('string(tipoalloggiato)', $guest);
                        $record = $current[$id] ?? null;
                        if (!$record || str_replace('-', '', (string) $record['arrivo']) !== $day || $record['tipo'] !== $type) {
                            $errors[] = 'Export storico #'.$export->id.', ospite '.$id.': eliminato o modificato arrivo/tipoalloggiato. Occorre una rettifica controllata su Ross1000; il solo XML ordinario non elimina il record precedente.';
                        }
                    }
                }
            } catch (\Throwable) {
                $errors[] = 'Export storico #'.$export->id.': formato precedente non riconciliabile automaticamente con il tracciato ufficiale.';
            }
        }
        return array_values(array_unique($errors));
    }
}
