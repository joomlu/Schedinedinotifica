<?php

namespace App\Http\Controllers;

use App\Models\Struttura;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class QaController extends Controller
{
    public function index()
    {
        return view('qa.index');
    }

    public function session(Request $request)
    {
        $user = $request->user();
        $sessionStrutturaId = $request->session()->get('struttura_corrente_id');
        $currentId = StrutturaCorrente::getId();
        $struttura = $currentId ? Struttura::find($currentId) : null;

        $servizio = null;
        if ($struttura) {
            $servizio = [
                'attiva' => (bool) $struttura->attiva,
                'scadenza_servizio' => $struttura->scadenza_servizio,
                'piano' => $struttura->piano,
                'stato_pagamento' => $struttura->stato_pagamento,
                'servizio_attivo' => $struttura->servizioAttivo(),
            ];
        }

        $impersonation = [
            'impersonator_id' => $request->session()->get('impersonator_id'),
            'impersonated_id' => $request->session()->get('impersonated_id'),
        ];

        return view('qa.session', [
            'user' => $user,
            'sessionStrutturaId' => $sessionStrutturaId,
            'currentId' => $currentId,
            'struttura' => $struttura,
            'servizio' => $servizio,
            'impersonation' => $impersonation,
        ]);
    }

    public function accesso()
    {
        $matrix = [
            'super_admin' => [
                ['name' => 'Dashboard', 'route' => 'root', 'expected' => 'OK'],
                ['name' => 'QA', 'route' => 'qa.index', 'expected' => 'OK'],
                ['name' => 'SuperAdmin panel', 'route' => 'superadmin.amministratori.index', 'expected' => 'OK'],
                ['name' => 'Admin area', 'route' => 'admin.proprietari.index', 'expected' => 'OK'],
                ['name' => 'Proprietario area', 'route' => 'proprietario.strutture.index', 'expected' => 'OK'],
            ],
            'admin' => [
                ['name' => 'Dashboard', 'route' => 'root', 'expected' => 'OK'],
                ['name' => 'SuperAdmin panel', 'route' => 'superadmin.amministratori.index', 'expected' => '403'],
                ['name' => 'Admin area', 'route' => 'admin.proprietari.index', 'expected' => 'OK'],
                ['name' => 'Proprietario area', 'route' => 'proprietario.strutture.index', 'expected' => '403'],
            ],
            'proprietario' => [
                ['name' => 'Dashboard', 'route' => 'root', 'expected' => 'OK'],
                ['name' => 'SuperAdmin panel', 'route' => 'superadmin.amministratori.index', 'expected' => '403'],
                ['name' => 'Admin area', 'route' => 'admin.proprietari.index', 'expected' => '403'],
                ['name' => 'Proprietario area', 'route' => 'proprietario.strutture.index', 'expected' => 'OK'],
            ],
            'struttura_user' => [
                ['name' => 'Dashboard', 'route' => 'root', 'expected' => 'OK'],
                ['name' => 'SuperAdmin panel', 'route' => 'superadmin.amministratori.index', 'expected' => '403'],
                ['name' => 'Admin area', 'route' => 'admin.proprietari.index', 'expected' => '403'],
                ['name' => 'Proprietario area', 'route' => 'proprietario.strutture.index', 'expected' => '403'],
            ],
        ];

        return view('qa.accesso', [
            'matrix' => $matrix,
        ]);
    }

    public function tenancy()
    {
        $tables = [
            'schedina',
            'clienti',
            'componenti',
            'tassa',
            'tassa_di_soggiorno',
            'schedina_camere',
        ];

        $currentId = StrutturaCorrente::getId();
        $summary = [];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $summary[$table] = [
                    'exists' => false,
                    'total' => null,
                    'by_struttura' => [],
                    'null_struttura' => null,
                    'current' => null,
                ];
                continue;
            }

            $total = DB::table($table)->count();
            $byStruttura = DB::table($table)
                ->select('struttura_id', DB::raw('count(*) as total'))
                ->groupBy('struttura_id')
                ->orderBy('struttura_id')
                ->get();
            $nullStruttura = DB::table($table)->whereNull('struttura_id')->count();
            $current = $currentId ? DB::table($table)->where('struttura_id', $currentId)->count() : null;

            $summary[$table] = [
                'exists' => true,
                'total' => $total,
                'by_struttura' => $byStruttura,
                'null_struttura' => $nullStruttura,
                'current' => $current,
            ];
        }

        return view('qa.tenancy', [
            'summary' => $summary,
            'currentId' => $currentId,
        ]);
    }
}
