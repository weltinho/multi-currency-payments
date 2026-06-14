<?php

return [
    'auth' => [
        'authenticated' => 'Autenticado',
        'invalid_credentials' => 'Las credenciales proporcionadas son incorrectas.',
    ],
    'payment' => [
        'forbidden' => 'No tiene permiso para realizar esta acción.',
        'not_found' => 'Pago no encontrado.',
        'not_pending' => 'Solo los pagos pendientes pueden aprobarse o rechazarse.',
        'rate_unavailable' => 'El tipo de cambio no está disponible temporalmente. Inténtelo de nuevo más tarde.',
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
