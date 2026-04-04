<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\LicenzaAssegnazione;
use App\Models\Struttura;

class PagamentiController extends Controller
{
    public function index()
    {
        $strutture = Struttura::orderBy('nome_struttura')->get(['id', 'nome_struttura', 'citta', 'provincia', 'attiva', 'scadenza_servizio', 'piano', 'stato_pagamento']);

        return view('superadmin.pagamenti.index', [
            'strutture' => $strutture,
        ]);
    }

    public function printAssegnazione(int $id)
    {
        $assegnazione = LicenzaAssegnazione::with(['articolo.parent', 'proprietario.admin', 'struttura'])->findOrFail($id);

        return view('superadmin.pagamenti.licenza-print', [
            'assegnazione' => $assegnazione,
        ]);
    }
}
