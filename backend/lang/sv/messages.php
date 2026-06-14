<?php

return [
    'auth' => [
        'authenticated' => 'Autentiserad',
        'invalid_credentials' => 'De angivna inloggningsuppgifterna är felaktiga.',
    ],
    'payment' => [
        'forbidden' => 'Du har inte behörighet att utföra denna åtgärd.',
        'not_found' => 'Betalningen hittades inte.',
        'not_pending' => 'Endast väntande betalningar kan godkännas eller avvisas.',
        'rate_unavailable' => 'Växelkursen är tillfälligt otillgänglig. Försök igen senare.',
        'unsupported_currency' => 'Din valuta stöds inte för växelkurskonvertering.',
    ],
    'employee' => [
        'forbidden' => 'Endast finansavdelningen kan hantera medarbetarkonton.',
        'country_not_supported' => 'Det valda landet stöds inte.',
        'validation' => [
            'name_required' => 'Medarbetarens namn krävs.',
            'email_required' => 'E-post krävs.',
            'email_invalid' => 'Ange en giltig e-postadress.',
            'email_taken' => 'Ett konto med denna e-post finns redan.',
            'password_required' => 'Lösenord krävs.',
            'password_min' => 'Lösenordet måste vara minst 6 tecken.',
            'country_required' => 'Land krävs.',
        ],
    ],
];
