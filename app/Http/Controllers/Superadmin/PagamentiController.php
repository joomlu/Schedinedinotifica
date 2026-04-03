<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
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
}
