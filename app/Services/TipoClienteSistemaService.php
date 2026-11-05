<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TipoClienteSistemaService
{
    public const CLASSIFICAZIONI = [
        'OSPITE' => 'Ospite',
        'COMPONENTE' => 'Componente',
        'RICHIESTA' => 'Richiesta',
    ];

    /** Allinea solo le tre classificazioni ufficiali, conservando ID e righe extra. */
    public function initialize(): void
    {
        DB::transaction(function () {
            foreach (self::CLASSIFICAZIONI as $code => $description) {
                $row = DB::table('tipo_cliente')->where('codice', $code)->lockForUpdate()->first();
                if ($row && $row->codice !== $code) {
                    throw new \RuntimeException('Codice Tipo cliente ambiguo: allineamento interrotto senza riclassificazione.');
                }
                if (!$row) {
                    DB::table('tipo_cliente')->insert([
                        'codice' => $code, 'descrizione' => $description, 'attivo' => true,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                } elseif ($row->descrizione !== $description || !$row->attivo) {
                    DB::table('tipo_cliente')->where('id', $row->id)->update([
                        'descrizione' => $description, 'attivo' => true, 'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
