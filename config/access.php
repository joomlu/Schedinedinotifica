<?php

return [
    // Solo connessioni esplicitamente abilitate dal superamministratore.
    // Nessun valore predefinito autorizza una struttura; conservare soltanto hash SHA-256.
    'connections' => json_decode(env('ACCESS_CONNECTIONS', '[]'), true) ?: [],
];
