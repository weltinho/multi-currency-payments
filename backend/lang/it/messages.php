<?php

return [
    'auth' => [
        'authenticated' => 'Autenticato',
        'invalid_credentials' => 'Le credenziali fornite non sono corrette.',
        'password_change_required' => 'Devi cambiare la password prima di continuare.',
        'password_changed' => 'Password aggiornata con successo.',
        'validation' => [
            'current_password_required' => 'La password attuale è obbligatoria.',
            'current_password_invalid' => 'La password attuale non è corretta.',
            'password_required' => 'La nuova password è obbligatoria.',
            'password_format' => 'La nuova password deve contenere esattamente 6 cifre.',
            'password_confirmed' => 'La conferma della password non corrisponde.',
        ],
    ],
    'payment' => [
        'forbidden' => 'Non sei autorizzato a eseguire questa azione.',
        'not_found' => 'Pagamento non trovato.',
        'not_pending' => 'Solo i pagamenti in sospeso possono essere approvati o rifiutati.',
        'rate_unavailable' => 'Il tasso di cambio non è temporaneamente disponibile. Riprova più tardi.',
        'rate_unavailable_missing_key' => 'Tasso di cambio non disponibile: imposta EXCHANGE_RATE_API_KEY in backend/.env (chiave gratuita su exchangerate-api.com).',
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
