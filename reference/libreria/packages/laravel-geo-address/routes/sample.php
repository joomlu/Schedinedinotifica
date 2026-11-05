<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('geo-sample')->group(function () {
    Route::get('/nations', function () {
        return response()->json([
            ['denominazione_nazione' => 'Italia', 'sigla_nazione' => 'IT'],
            ['denominazione_nazione' => 'Spagna', 'sigla_nazione' => 'ES'],
        ]);
    });

    Route::get('/regions', function (Request $r) {
        $all = [
            ['codice_regione' => '08', 'denominazione_regione' => 'Emilia-Romagna'],
            ['codice_regione' => '09', 'denominazione_regione' => 'Toscana'],
        ];
        $q = $r->query('q');
        if ($q) {
            $all = array_values(array_filter($all, fn ($x) => str_contains($x['denominazione_regione'], $q) || $x['codice_regione'] === $q));
        }

        return response()->json($all);
    });

    Route::get('/provinces-all', function (Request $r) {
        $reg = $r->query('codice_regione');
        $all = [
            ['sigla_provincia' => 'RN', 'denominazione_provincia' => 'Rimini', 'codice_regione' => '08'],
            ['sigla_provincia' => 'FI', 'denominazione_provincia' => 'Firenze', 'codice_regione' => '09'],
        ];
        if ($reg) {
            $all = array_values(array_filter($all, fn ($x) => $x['codice_regione'] === $reg));
        }
        $q = $r->query('q');
        if ($q) {
            $all = array_values(array_filter($all, fn ($x) => str_contains($x['sigla_provincia'], strtoupper($q))));
        }

        return response()->json($all);
    });

    Route::get('/cities-by-province', function (Request $r) {
        $sigla = strtoupper((string) $r->query('sigla_provincia'));
        $by = [
            'RN' => [
                ['denominazione_ita' => 'Rimini', 'sigla_provincia' => 'RN', 'codice_regione' => '08', 'codice_istat' => '099014'],
                ['denominazione_ita' => 'Bellaria Igea Marina', 'sigla_provincia' => 'RN', 'codice_regione' => '08', 'codice_istat' => '099001'],
            ],
            'FI' => [
                ['denominazione_ita' => 'Firenze', 'sigla_provincia' => 'FI', 'codice_regione' => '09', 'codice_istat' => '048017'],
            ],
        ];

        return response()->json($by[$sigla] ?? []);
    });

    Route::get('/cap-by-province', function (Request $r) {
        $sigla = strtoupper((string) $r->query('sigla_provincia'));
        $comune = $r->query('comune');
        $q = $r->query('q');
        $by = [
            'RN' => [
                ['cap' => '47921', 'sigla_provincia' => 'RN', 'denominazione_ita' => 'Rimini', 'codice_istat' => '099014'],
                ['cap' => '47814', 'sigla_provincia' => 'RN', 'denominazione_ita' => 'Bellaria Igea Marina', 'codice_istat' => '099001'],
            ],
            'FI' => [
                ['cap' => '50121', 'sigla_provincia' => 'FI', 'denominazione_ita' => 'Firenze', 'codice_istat' => '048017'],
            ],
        ];
        $res = $by[$sigla] ?? [];
        if ($comune) {
            $res = array_values(array_filter($res, fn ($x) => $x['denominazione_ita'] === $comune));
        }
        if ($q) {
            $res = array_values(array_filter($res, fn ($x) => str_contains($x['cap'], $q)));
        }

        return response()->json($res);
    });
});
