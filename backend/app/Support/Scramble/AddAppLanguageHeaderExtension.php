<?php

namespace App\Support\Scramble;

use App\Enums\AppLocale;
use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;

/** Documents X-App-Language on API operations (Try It + language selector on /docs/api). */
class AddAppLanguageHeaderExtension extends OperationExtension
{
    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        if (! str_starts_with($routeInfo->route->uri(), 'api')) {
            return;
        }

        $type = new StringType;
        $type->enum = AppLocale::values();
        $type->example = 'en';

        $operation->addParameters([
            Parameter::make('X-App-Language', 'header')
                ->description(
                    'Locale for translated validation errors and API messages. '
                    .'Use the **Language** control on this docs page (stored separately from the UI app).'
                )
                ->setSchema(Schema::fromType($type)),
        ]);
    }
}
