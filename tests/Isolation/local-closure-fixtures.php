<?php

require __DIR__.'/legacy-user-fixtures.php';
$csv = "id;valore\n".str_repeat("1;dato-sintetico-effimero\n", 20000);
file_put_contents(public_path('proxy-synthetic.csv'), $csv);
file_put_contents(public_path('proxy-synthetic-ids.json'), json_encode(['bytes' => strlen($csv), 'sha256' => hash('sha256', $csv), 'righe' => 20001]));
