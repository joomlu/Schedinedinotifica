<?php
// Solo namespace privato: vendor approvato montato readonly, nessun bootstrap o DB.
require '/vendor/autoload.php';
$factory = new Illuminate\Validation\Factory(new Illuminate\Translation\Translator(new Illuminate\Translation\ArrayLoader(), 'it'));
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
$rows = [];
foreach (['avatar.png', 'avatar.php', 'avatar.PHP', 'avatar.phtml', 'avatar.php.jpg'] as $name) {
    $path = '/work/fixture-'.count($rows);
    file_put_contents($path, $png.'<?php echo "PROVA_SINTETICA"; ?>');
    $file = new Illuminate\Http\UploadedFile($path, $name, null, UPLOAD_ERR_OK, true);
    $valid = $factory->make(['avatar' => $file], ['avatar' => ['image', 'mimes:jpg,jpeg,png', 'max:1024']])->passes();
    $safe = $file->extension();
    $expectedValid = !in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), ['php', 'phtml'], true);
    if ($valid !== $expectedValid || $safe !== 'png') {
        throw new RuntimeException('Regressione estensione MIME');
    }
    $rows[] = ['nome_sintetico' => $name, 'validazione_contenuto' => $valid, 'estensione_originale' => $file->getClientOriginalExtension(), 'estensione_corretta' => $safe];
}
file_put_contents('/work/non-immagine', '<?php echo "PROVA_SINTETICA"; ?>');
$bad = new Illuminate\Http\UploadedFile('/work/non-immagine', 'avatar.png', null, UPLOAD_ERR_OK, true);
if ($factory->make(['avatar' => $bad], ['avatar' => ['image', 'mimes:jpg,jpeg,png']])->passes()) {
    throw new RuntimeException('Non immagine accettata');
}
echo json_encode(['esito' => 'PASS', 'casi' => $rows, 'non_immagine_rifiutata' => true, 'nessun_bootstrap_DB_o_trasmissione' => true]), "\n";
