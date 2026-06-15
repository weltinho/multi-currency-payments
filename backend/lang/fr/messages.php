<?php

return [
    'auth' => [
        'authenticated' => 'Authentifié',
        'invalid_credentials' => 'Les identifiants fournis sont incorrects.',
        'password_change_required' => 'Vous devez changer votre mot de passe avant de continuer.',
        'password_changed' => 'Mot de passe mis à jour avec succès.',
        'validation' => [
            'current_password_required' => 'Le mot de passe actuel est requis.',
            'current_password_invalid' => 'Le mot de passe actuel est incorrect.',
            'password_required' => 'Le nouveau mot de passe est requis.',
            'password_format' => 'Le nouveau mot de passe doit contenir exactement 6 chiffres.',
            'password_confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ],
    ],
    'payment' => [
        'forbidden' => 'Vous n\'êtes pas autorisé à effectuer cette action.',
        'not_found' => 'Paiement introuvable.',
        'not_pending' => 'Seuls les paiements en attente peuvent être approuvés ou rejetés.',
        'rate_unavailable' => 'Le taux de change est temporairement indisponible. Veuillez réessayer plus tard.',
        'rate_unavailable_missing_key' => 'Taux de change indisponible : définissez EXCHANGE_RATE_API_KEY dans backend/.env (clé gratuite sur exchangerate-api.com).',
        'unsupported_currency' => 'Votre devise n\'est pas prise en charge pour la conversion du taux de change.',
    ],
    'employee' => [
        'forbidden' => 'Seule l\'équipe financière peut gérer les comptes des employés.',
        'country_not_supported' => 'Le pays sélectionné n\'est pas pris en charge.',
        'validation' => [
            'name_required' => 'Le nom de l\'employé est requis.',
            'email_required' => 'L\'e-mail est requis.',
            'email_invalid' => 'Saisissez une adresse e-mail valide.',
            'email_taken' => 'Un compte avec cet e-mail existe déjà.',
            'password_required' => 'Le mot de passe est requis.',
            'password_min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'country_required' => 'Le pays est requis.',
        ],
    ],
];
