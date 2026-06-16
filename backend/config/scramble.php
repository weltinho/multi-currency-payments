<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

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

    /*
     * null = Scramble derives the API server from Laravel's url('api'), which uses APP_URL.
     * CSRF / overview links use the same value via ScrambleConfigurator::gatewayUrl().
     */
    'servers' => null,

    'enum_cases_description_strategy' => 'description',

    'enum_cases_names_strategy' => false,

    'flatten_deep_query_parameters' => true,

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [
        App\Support\Scramble\DemoTestUsersOperationExtension::class,
        App\Support\Scramble\AddAppLanguageHeaderExtension::class,
    ],
];
