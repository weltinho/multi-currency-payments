<?php

return [
    'auth' => [
        'authenticated' => 'Authentifiziert',
        'invalid_credentials' => 'Die angegebenen Zugangsdaten sind falsch.',
    ],
    'payment' => [
        'forbidden' => 'Sie sind nicht berechtigt, diese Aktion auszuführen.',
        'not_found' => 'Zahlung nicht gefunden.',
        'not_pending' => 'Nur ausstehende Zahlungen können genehmigt oder abgelehnt werden.',
        'rate_unavailable' => 'Der Wechselkurs ist vorübergehend nicht verfügbar. Bitte versuchen Sie es später erneut.',
        'unsupported_currency' => 'Ihre Währung wird für die Wechselkursumrechnung nicht unterstützt.',
    ],
    'employee' => [
        'forbidden' => 'Nur das Finanzteam kann Mitarbeiterkonten verwalten.',
        'country_not_supported' => 'Das ausgewählte Land wird nicht unterstützt.',
        'validation' => [
            'name_required' => 'Der Name des Mitarbeiters ist erforderlich.',
            'email_required' => 'E-Mail ist erforderlich.',
            'email_invalid' => 'Geben Sie eine gültige E-Mail-Adresse ein.',
            'email_taken' => 'Ein Konto mit dieser E-Mail existiert bereits.',
            'password_required' => 'Passwort ist erforderlich.',
            'password_min' => 'Das Passwort muss mindestens 6 Zeichen lang sein.',
            'country_required' => 'Land ist erforderlich.',
        ],
    ],
];
