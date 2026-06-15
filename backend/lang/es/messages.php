<?php

return [
    'auth' => [
        'authenticated' => 'Autenticado',
        'invalid_credentials' => 'Las credenciales proporcionadas son incorrectas.',
        'password_change_required' => 'Debe cambiar su contraseña antes de continuar.',
        'password_changed' => 'Contraseña actualizada correctamente.',
        'validation' => [
            'current_password_required' => 'La contraseña actual es obligatoria.',
            'current_password_invalid' => 'La contraseña actual es incorrecta.',
            'password_required' => 'La nueva contraseña es obligatoria.',
            'password_format' => 'La nueva contraseña debe tener exactamente 6 dígitos.',
            'password_confirmed' => 'La confirmación de la contraseña no coincide.',
        ],
    ],
    'payment' => [
        'forbidden' => 'No tiene permiso para realizar esta acción.',
        'not_found' => 'Pago no encontrado.',
        'not_pending' => 'Solo los pagos pendientes pueden aprobarse o rechazarse.',
        'rate_unavailable' => 'El tipo de cambio no está disponible temporalmente. Inténtelo de nuevo más tarde.',
        'rate_unavailable_missing_key' => 'Tipo de cambio no disponible: configure EXCHANGE_RATE_API_KEY en backend/.env (clave gratuita en exchangerate-api.com).',
        'unsupported_currency' => 'Su moneda no es compatible con la conversión de tipo de cambio.',
    ],
    'employee' => [
        'forbidden' => 'Solo el equipo financiero puede gestionar cuentas de empleados.',
        'country_not_supported' => 'El país seleccionado no es compatible.',
        'validation' => [
            'name_required' => 'El nombre del empleado es obligatorio.',
            'email_required' => 'El correo electrónico es obligatorio.',
            'email_invalid' => 'Introduzca una dirección de correo válida.',
            'email_taken' => 'Ya existe una cuenta con este correo.',
            'password_required' => 'La contraseña es obligatoria.',
            'password_min' => 'La contraseña debe tener al menos 6 caracteres.',
            'country_required' => 'El país es obligatorio.',
        ],
    ],
];
