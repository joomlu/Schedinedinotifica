<?php

return [
    // Attivare solo dopo la qualifica isolata e l'installazione approvata.
    'enabled' => (bool) env('CUSTOMER_SHARING_ENABLED', false),
];
