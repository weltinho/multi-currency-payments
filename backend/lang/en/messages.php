<?php

return [
    'auth' => [
        'authenticated' => 'Authenticated',
        'invalid_credentials' => 'The provided credentials are incorrect.',
    ],
    'payment' => [
        'forbidden' => 'You are not allowed to perform this action.',
        'not_found' => 'Payment not found.',
        'not_pending' => 'Only pending payments can be approved or rejected.',
        'rate_unavailable' => 'Exchange rate is temporarily unavailable. Please try again later.',
        'unsupported_currency' => 'Your currency is not supported for exchange rate conversion.',
    ],
    'employee' => [
        'forbidden' => 'Only the finance team can manage employee accounts.',
        'country_not_supported' => 'The selected country is not supported.',
        'validation' => [
            'name_required' => 'Employee name is required.',
            'email_required' => 'Email is required.',
            'email_invalid' => 'Enter a valid email address.',
            'email_taken' => 'An account with this email already exists.',
            'password_required' => 'Password is required.',
            'password_min' => 'Password must be at least 6 characters.',
            'country_required' => 'Country is required.',
        ],
    ],
];
