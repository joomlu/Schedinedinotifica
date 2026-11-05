<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Models\Proprietario;
use App\Support\StrutturaCorrente;

class DemoMapController extends Controller
{
    public function index()
    {
        $proprietari = Proprietario::with(['admin', 'strutture'])->orderBy('id')->get();
        $rows = [];

        foreach ($proprietari as $owner) {
            $structure = $owner->strutture->first();
            $rows[] = [
                'admin' => $owner->admin,
                'owner' => $owner,
                'structure' => $structure,
                'location' => $structure ? $this->structureLocation($structure) : null,
            ];
        }

        return view('qa.demo-map', [
            'rows' => $rows,
            'currentStrutturaId' => StrutturaCorrente::getId(),
        ]);
    }

    protected function structureLocation($structure): string
    {
        $city = $structure->{'città'} ?? $structure->citta ?? null;
        $prov = $structure->provincia ?? null;
        $parts = array_filter([$city, $prov], fn ($v) => $v !== null && $v !== '');
        return empty($parts) ? '' : implode(' - ', $parts);
    }
}
