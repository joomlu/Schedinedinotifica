<?php

return [
    // Canale esplicitamente abilitato; il mailer generale e i provider restano invariati.
    'email_abilitata' => env('WEB_CHECKIN_MAIL_ENABLED', false),
    'mailer' => env('WEB_CHECKIN_MAILER', 'web_checkin_smtp'),
];
