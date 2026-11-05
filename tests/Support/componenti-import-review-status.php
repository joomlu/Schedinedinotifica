<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Services\ComponentiImportService;
use App\Support\Componenti\DatiComponenteNormalizzati;

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, "FAIL: {$message}\nExpected: " . var_export($expected, true) . "\nActual:   " . var_export($actual, true) . "\n");
        exit(1);
    }
}

DatiComponenteNormalizzati::impostaGeoNazioneResolverPerTest(function (string $value): ?array {
    $normalized = strtoupper(trim($value));
    $lookup = preg_replace('/[^A-Z0-9]+/', ' ', $normalized);
    $lookup = trim((string) $lookup);

    $map = [
        'ITALIA' => ['nome' => 'Italia', 'cittadinanza' => 'Italiana', 'is_italia' => true],
        'FRANCIA' => ['nome' => 'Francia', 'cittadinanza' => 'Francese', 'is_italia' => false],
    ];

    return $map[$lookup] ?? null;
});

$completo = [
    'name' => 'Mario',
    'surname' => 'Rossi',
    'sex' => 'M',
    'relationship' => 'MEMBRO GRUPPO',
    'exent' => 'NO',
    'city_nac' => 'Italiana',
    'country_nac' => 'Italia',
    'date_nac' => '15/02/1980',
    'province_nac' => 'RM',
    'comune_nac' => 'Roma',
];

$statoCompleto = DatiComponenteNormalizzati::classificaRiga($completo);
assertSameValue(DatiComponenteNormalizzati::STATO_COMPLETO, $statoCompleto['status'], 'Riga completa deve restituire stato COMPLETO.');

$daVerificare = [
    'name' => 'Luisa',
    'surname' => 'Bianchi',
    'sex' => 'F',
    'relationship' => 'MEMBRO GRUPPO',
    'exent' => 'NO',
    'country_nac' => 'Italia',
    'date_nac' => '15/02/1980',
    'province_nac' => 'RM',
    'comune_nac' => 'Roma',
];

$statoVerifica = DatiComponenteNormalizzati::classificaRiga($daVerificare);
assertSameValue(DatiComponenteNormalizzati::STATO_DA_VERIFICARE, $statoVerifica['status'], 'La cittadinanza mancante su Italia deve essere proposta come DA_VERIFICARE.');
assertSameValue('Italiana', $statoVerifica['proposta']['city_nac'] ?? null, 'La cittadinanza proposta deve essere Italiana.');

$daCompletare = [
    'name' => 'Anna',
    'surname' => 'Verdi',
    'sex' => 'F',
    'relationship' => 'MEMBRO GRUPPO',
    'exent' => 'NO',
    'country_nac' => 'Italia',
    'date_nac' => '',
    'province_nac' => 'RM',
    'comune_nac' => 'Roma',
];

$statoCompletare = DatiComponenteNormalizzati::classificaRiga($daCompletare);
assertSameValue(DatiComponenteNormalizzati::STATO_DA_COMPLETARE, $statoCompletare['status'], 'Data di nascita mancante deve essere DA_COMPLETARE.');

$nonImportabile = [
    'name' => 'Test',
    'surname' => 'Nazione',
    'sex' => 'M',
    'relationship' => 'MEMBRO GRUPPO',
    'exent' => 'NO',
    'country_nac' => 'Nazione Fantasma',
    'date_nac' => '15/02/1980',
];

$statoNonImportabile = DatiComponenteNormalizzati::classificaRiga($nonImportabile);
assertSameValue(DatiComponenteNormalizzati::STATO_NON_IMPORTABILE, $statoNonImportabile['status'], 'Nazione non riconosciuta deve essere NON_IMPORTABILE.');

$service = new ComponentiImportService();
$preview = $service->previewDaContenuto(
    "Nome;Cognome;Sesso;Nazione nascita;Data di nascita;Provincia nascita;Comune nascita;Cittadinanza;Nazione residenza;Provincia residenza;Comune residenza;Tipo via;Indirizzo;Numero civico;CAP\nMario;Rossi;M;Italia;15/02/1980;RM;Roma;Italiana;Italia;RM;Roma;Via;Via Roma;12;00100\nLuisa;Bianchi;F;Italia;15/02/1980;RM;Roma;;Italia;RM;Roma;Via;Via Roma;13;00100\n",
    'csv',
    fn (): array => [
        ['codice' => 20, 'descrizione' => 'MEMBRO GRUPPO'],
    ]
);
assertSameValue(2, count($preview['rows']), 'L analisi deve contenere due righe preview.');
assertSameValue(DatiComponenteNormalizzati::STATO_COMPLETO, $preview['rows'][0]['status'], 'Prima riga completa deve essere COMPLETO.');
assertSameValue(DatiComponenteNormalizzati::STATO_DA_VERIFICARE, $preview['rows'][1]['status'], 'Seconda riga con cittadinanza mancante deve essere DA_VERIFICARE.');

fwrite(STDOUT, "PASS: review status integration ok\n");
