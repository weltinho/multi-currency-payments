<?php

return [
    'auth' => [
        'authenticated' => 'Autenticato',
        'invalid_credentials' => 'Le credenziali fornite non sono corrette.',
    ],
    'payment' => [
        'forbidden' => 'Non sei autorizzato a eseguire questa azione.',
        'not_found' => 'Pagamento non trovato.',
        'not_pending' => 'Solo i pagamenti in sospeso possono essere approvati o rifiutati.',
        'rate_unavailable' => 'Il tasso di cambio non è temporaneamente disponibile. Riprova più tardi.',
        'unsupported_currency' => 'La tua valuta non è supportata per la conversione del tasso di cambio.',
    ],
    'employee' => [
        'forbidden' => 'Solo il team finanziario può gestire gli account dei dipendenti.',
        'country_not_supported' => 'Il paese selezionato non è supportato.',
        'validation' => [
            'name_required' => 'Il nome del dipendente è obbligatorio.',
            'email_required' => 'L\'email è obbligatoria.',
            'email_invalid' => 'Inserisci un indirizzo email valido.',
            'email_taken' => 'Esiste già un account con questa email.',
            'password_required' => 'La password è obbligatoria.',
            'password_min' => 'La password deve contenere almeno 6 caratteri.',
            'country_required' => 'Il paese è obbligatorio.',
        ],
    ],
];
