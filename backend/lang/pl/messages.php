<?php

return [
    'auth' => [
        'authenticated' => 'Uwierzytelniono',
        'invalid_credentials' => 'Podane dane logowania są nieprawidłowe.',
    ],
    'payment' => [
        'forbidden' => 'Nie masz uprawnień do wykonania tej operacji.',
        'not_found' => 'Nie znaleziono płatności.',
        'not_pending' => 'Tylko oczekujące płatności mogą zostać zatwierdzone lub odrzucone.',
        'rate_unavailable' => 'Kurs wymiany jest tymczasowo niedostępny. Spróbuj ponownie później.',
        'unsupported_currency' => 'Twoja waluta nie jest obsługiwana do przeliczania kursu wymiany.',
    ],
    'employee' => [
        'forbidden' => 'Tylko zespół finansowy może zarządzać kontami pracowników.',
        'country_not_supported' => 'Wybrany kraj nie jest obsługiwany.',
        'validation' => [
            'name_required' => 'Imię i nazwisko pracownika jest wymagane.',
            'email_required' => 'Adres e-mail jest wymagany.',
            'email_invalid' => 'Wprowadź prawidłowy adres e-mail.',
            'email_taken' => 'Konto z tym adresem e-mail już istnieje.',
            'password_required' => 'Hasło jest wymagane.',
            'password_min' => 'Hasło musi mieć co najmniej 6 znaków.',
            'country_required' => 'Kraj jest wymagany.',
        ],
    ],
];
