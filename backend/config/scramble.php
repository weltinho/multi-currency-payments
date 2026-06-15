<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

$appUrl = rtrim((string) env('APP_URL', 'http://localhost:8080'), '/');

return [
    'api_path' => 'api',

    'api_domain' => null,

    'export_path' => 'api.json',

    'info' => [
        'version' => env('API_VERSION', '1.0.0'),
        'description' => '',
        'title' => 'Buzzvel Multi-Currency Payments API',
    ],

    'ui' => [
        'title' => 'Buzzvel Multi-Currency Payments API',
        'theme' => 'dark',
        'hide_try_it' => false,
        'hide_schemas' => false,
        'logo' => '',
        'try_it_credentials_policy' => 'include',
        'layout' => 'responsive',
    ],

    'servers' => [
        'Local' => "{$appUrl}/api",
    ],

    'enum_cases_description_strategy' => 'description',

    'enum_cases_names_strategy' => false,

    'flatten_deep_query_parameters' => true,

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [
        App\Support\Scramble\DemoTestUsersOperationExtension::class,
    ],
];
