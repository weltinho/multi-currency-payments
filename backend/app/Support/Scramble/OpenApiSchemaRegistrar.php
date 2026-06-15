<?php

namespace App\Support\Scramble;

use App\OpenApi\ChangePasswordRequest;
use App\OpenApi\CountryProfileListResponse;
use App\OpenApi\CountryProfileResponse;
use App\OpenApi\EmployeeListResponse;
use App\OpenApi\EmployeeResponse;
use App\OpenApi\HealthResponse;
use App\OpenApi\MessageResponse;
use App\OpenApi\PaginatedPaymentResponse;
use App\OpenApi\PaymentDecisionRequest;
use App\OpenApi\PaymentResponse;
use App\OpenApi\PaymentSummaryResponse;
use App\OpenApi\StoreEmployeeRequest;
use App\OpenApi\StorePaymentRequest;
use App\OpenApi\TestUsersResponse;
use App\OpenApi\UserResponse;
use App\Support\SupportedCurrencies;
use Dedoc\Scramble\Contracts\DocumentTransformer;
use Dedoc\Scramble\OpenApiContext;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\RequestBodyObject;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\IntegerType;
use Dedoc\Scramble\Support\Generator\Types\NumberType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;

/**
 * Registers fully populated request/response schemas for the Scramble sidebar.
 *
 * FormRequest classes register empty shells before rules merge — this overwrites them.
 */
final class OpenApiSchemaRegistrar implements DocumentTransformer
{
    public function handle(OpenApi $document, OpenApiContext $context): void
    {
        foreach (self::definitions() as $name => $schema) {
            $schema->setTitle($name);
            $document->components->addSchema($name, $schema);
        }

        self::bindOperationSchemas($document);
    }

    private static function bindOperationSchemas(OpenApi $document): void
    {
        /** @var array<string, array{responses?: array<int, class-string>, request?: class-string}> $bindings */
        $bindings = [
            'health' => ['responses' => [200 => HealthResponse::class]],
            'testUser.index' => ['responses' => [200 => TestUsersResponse::class]],
            'auth.login' => ['responses' => [200 => MessageResponse::class]],
            'auth.user' => ['responses' => [200 => UserResponse::class]],
            'auth.updatePassword' => [
                'request' => ChangePasswordRequest::class,
                'responses' => [200 => UserResponse::class],
            ],
            'employee.index' => ['responses' => [200 => EmployeeListResponse::class]],
            'employee.store' => [
                'request' => StoreEmployeeRequest::class,
                'responses' => [201 => UserResponse::class],
            ],
            'employee.countries' => ['responses' => [200 => CountryProfileListResponse::class]],
            'payment.index' => ['responses' => [200 => PaginatedPaymentResponse::class]],
            'payment.summary' => ['responses' => [200 => PaymentSummaryResponse::class]],
            'payment.store' => [
                'request' => StorePaymentRequest::class,
                'responses' => [201 => PaymentResponse::class],
            ],
            'payment.show' => ['responses' => [200 => PaymentResponse::class]],
            'payment.decide' => [
                'request' => PaymentDecisionRequest::class,
                'responses' => [200 => PaymentResponse::class],
            ],
        ];

        foreach ($document->paths as $path) {
            foreach ($path->operations as $operation) {
                $binding = $bindings[$operation->operationId ?? ''] ?? null;
                if ($binding === null) {
                    continue;
                }

                if (isset($binding['request'])) {
                    self::bindRequestBody($document, $operation, $binding['request']);
                }

                foreach ($binding['responses'] ?? [] as $status => $schemaClass) {
                    self::bindResponseBody($document, $operation, $status, $schemaClass);
                }
            }
        }
    }

    /** @param class-string $schemaClass */
    private static function bindRequestBody(OpenApi $document, \Dedoc\Scramble\Support\Generator\Operation $operation, string $schemaClass): void
    {
        $operation->addRequestBodyObject(
            RequestBodyObject::make()
                ->required(true)
                ->setContent('application/json', self::schemaReference($document, $schemaClass)),
        );
    }

    /** @param class-string $schemaClass */
    private static function bindResponseBody(OpenApi $document, \Dedoc\Scramble\Support\Generator\Operation $operation, int $status, string $schemaClass): void
    {
        $ref = self::schemaReference($document, $schemaClass);

        foreach ($operation->responses as $index => $response) {
            $resolved = $response instanceof Reference ? $response->resolve() : $response;
            if (! $resolved instanceof Response || (int) $resolved->code !== $status) {
                continue;
            }

            $resolved->setContent('application/json', $ref);
            $operation->responses[$index] = $resolved;

            return;
        }
    }

    /** @param class-string $schemaClass */
    private static function schemaReference(OpenApi $document, string $schemaClass): Reference
    {
        $name = class_basename($schemaClass);

        return new Reference('schemas', $name, $document->components, $name);
    }

    /** @return array<string, Schema> */
    private static function definitions(): array
    {
        return [
            class_basename(ChangePasswordRequest::class) => self::changePasswordRequest(),
            class_basename(StoreEmployeeRequest::class) => self::storeEmployeeRequest(),
            class_basename(StorePaymentRequest::class) => self::storePaymentRequest(),
            class_basename(PaymentDecisionRequest::class) => self::paymentDecisionRequest(),
            class_basename(MessageResponse::class) => self::messageResponse(),
            class_basename(HealthResponse::class) => self::healthResponse(),
            class_basename(UserResponse::class) => self::userResponse(),
            class_basename(PaymentResponse::class) => self::paymentResponse(),
            class_basename(PaginatedPaymentResponse::class) => self::paginatedPaymentResponse(),
            class_basename(PaymentSummaryResponse::class) => self::paymentSummaryResponse(),
            class_basename(TestUsersResponse::class) => self::testUsersResponse(),
            class_basename(EmployeeResponse::class) => self::employeeResponse(),
            class_basename(EmployeeListResponse::class) => self::employeeListResponse(),
            class_basename(CountryProfileResponse::class) => self::countryProfileResponse(),
            class_basename(CountryProfileListResponse::class) => self::countryProfileListResponse(),
        ];
    }

    private static function changePasswordRequest(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('current_password', self::str('123456'))
            ->addProperty('password', self::str('SecurePass1!', 'Six digits — see PasswordPolicy'))
            ->addProperty('password_confirmation', self::str('SecurePass1!'))
            ->setRequired(['current_password', 'password', 'password_confirmation']);

        return self::schema($type, 'Change password after first login');
    }

    private static function storeEmployeeRequest(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('name', self::str('Jordan Lee'))
            ->addProperty('email', self::str('jordan.lee@buzzvel.com')->format('email'))
            ->addProperty('country_code', self::str('US', 'ISO 3166-1 alpha-2 — sets profile currency'))
            ->setRequired(['name', 'email', 'country_code']);

        return self::schema($type, 'Finance provisions a new employee');
    }

    private static function storePaymentRequest(): Schema
    {
        $currency = self::str('BRL', 'Optional — defaults to employee profile currency');
        $currency->enum = SupportedCurrencies::codes();

        $type = (new ObjectType)
            ->addProperty('description', self::str('Equipment reimbursement — monitor and peripherals', 'Max 1000 chars'))
            ->addProperty('local_amount', (new NumberType)->format('float')->example(4200))
            ->addProperty('currency', $currency)
            ->setRequired(['description', 'local_amount']);

        return self::schema($type, 'Employee reimbursement request');
    }

    private static function paymentDecisionRequest(): Schema
    {
        $status = self::str('approved', 'Finance decision — approved (green) or rejected (red)');
        $status->enum = ['approved', 'rejected'];

        $type = (new ObjectType)
            ->addProperty('status', $status)
            ->setRequired(['status']);

        return self::schema($type, 'Approve or reject a pending payment');
    }

    private static function messageResponse(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('message', self::str('Authenticated'))
            ->setRequired(['message']);

        return self::schema($type, 'Localized success message');
    }

    private static function healthResponse(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('status', self::str('ok'))
            ->setRequired(['status']);

        return self::schema($type, 'Liveness probe');
    }

    private static function userResponse(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('id', self::str('01932a1b-2c3d-7000-8000-000000000001'))
            ->addProperty('name', self::str('Helena Marques'))
            ->addProperty('email', self::str('finance@buzzvel.com'))
            ->addProperty('role', self::str('finance', 'finance | employee'))
            ->addProperty('country', self::str('Portugal'))
            ->addProperty('country_code', self::str('PT'))
            ->addProperty('currency', self::str('EUR'))
            ->addProperty('must_change_password', (new \Dedoc\Scramble\Support\Generator\Types\BooleanType)->example(false))
            ->setRequired(['id', 'name', 'email', 'role', 'country', 'country_code', 'currency', 'must_change_password']);

        return self::schema($type, 'Authenticated user profile');
    }

    private static function paymentResponse(): Schema
    {
        $status = self::str('pending', 'pending | approved | rejected | expired');
        $status->enum = ['pending', 'approved', 'rejected', 'expired'];

        $type = (new ObjectType)
            ->addProperty('id', self::str('01932a1b-2c3d-7000-8000-000000000501'))
            ->addProperty('reference', self::str('PAY-2026-1007'))
            ->addProperty('user_id', self::str('01932a1b-2c3d-7000-8000-000000000010'))
            ->addProperty('user_name', self::str('Rafael Silva'))
            ->addProperty('country', self::str('Brazil'))
            ->addProperty('currency', self::str('BRL'))
            ->addProperty('local_amount', (new NumberType)->format('float')->example(4200))
            ->addProperty('exchange_rate', (new NumberType)->format('float')->example(6.21))
            ->addProperty('eur_amount', (new NumberType)->format('float')->example(676.33))
            ->addProperty('status', $status)
            ->addProperty('created_at', self::str('2026-06-15T08:00:00+00:00'))
            ->addProperty('updated_at', self::str('2026-06-15T08:00:00+00:00'))
            ->addProperty('reviewed_at', self::str('')->nullable(true))
            ->addProperty('rate_source', self::str('exchangerate-api.com'))
            ->addProperty('description', self::str('Equipment reimbursement — monitor and peripherals'))
            ->setRequired([
                'id', 'reference', 'user_id', 'user_name', 'country', 'currency',
                'local_amount', 'exchange_rate', 'eur_amount', 'status',
                'created_at', 'updated_at', 'rate_source', 'description',
            ]);

        return self::schema($type, 'Payment request resource');
    }

    private static function paginatedPaymentResponse(): Schema
    {
        $paymentItems = new ArrayType;
        $paymentItems->items = self::paymentResponse()->type;

        $type = (new ObjectType)
            ->addProperty('data', $paymentItems)
            ->addProperty('current_page', (new IntegerType)->example(1))
            ->addProperty('last_page', (new IntegerType)->example(1))
            ->addProperty('per_page', (new IntegerType)->example(8))
            ->addProperty('total', (new IntegerType)->example(12))
            ->addProperty('from', (new IntegerType)->example(1))
            ->addProperty('to', (new IntegerType)->example(8))
            ->setRequired(['data', 'current_page', 'last_page', 'per_page', 'total', 'from', 'to']);

        return self::schema($type, 'Paginated payment list');
    }

    private static function paymentSummaryResponse(): Schema
    {
        $counts = (new ObjectType)
            ->addProperty('all', (new IntegerType)->example(12))
            ->addProperty('pending', (new IntegerType)->example(4))
            ->addProperty('approved', (new IntegerType)->example(5))
            ->addProperty('rejected', (new IntegerType)->example(1))
            ->addProperty('expired', (new IntegerType)->example(2))
            ->setRequired(['all', 'pending', 'approved', 'rejected', 'expired']);

        $type = (new ObjectType)
            ->addProperty('status_counts', $counts)
            ->addProperty('approved_eur_total', (new NumberType)->format('float')->example(1842.59))
            ->setRequired(['status_counts', 'approved_eur_total']);

        return self::schema($type, 'Finance dashboard summary cards');
    }

    private static function testUsersResponse(): Schema
    {
        $finance = new ArrayType;
        $finance->items = self::accountType('Helena Marques', 'finance@buzzvel.com', 'Portugal', 'EUR');

        $employees = new ArrayType;
        $employees->items = self::accountType('Rafael Silva', 'rafael@buzzvel.com', 'Brazil', 'BRL');

        $type = (new ObjectType)
            ->addProperty('finance', $finance)
            ->addProperty('employees', $employees)
            ->setRequired(['finance', 'employees']);

        return self::schema($type, 'Demo only — grouped evaluator accounts');
    }

    private static function accountType(string $name, string $email, string $country, string $currency): ObjectType
    {
        return (new ObjectType)
            ->addProperty('name', self::str($name))
            ->addProperty('email', self::str($email))
            ->addProperty('country', self::str($country))
            ->addProperty('currency', self::str($currency))
            ->setRequired(['name', 'email', 'country', 'currency']);
    }

    private static function employeeResponse(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('id', self::str('01932a1b-2c3d-7000-8000-000000000010'))
            ->addProperty('name', self::str('Rafael Silva'))
            ->addProperty('email', self::str('rafael@buzzvel.com'))
            ->addProperty('country', self::str('Brazil'))
            ->addProperty('country_code', self::str('BR'))
            ->addProperty('currency', self::str('BRL'))
            ->setRequired(['id', 'name', 'email', 'country', 'country_code', 'currency']);

        return self::schema($type, 'Employee list row');
    }

    private static function employeeListResponse(): Schema
    {
        $items = new ArrayType;
        $items->items = self::employeeResponse()->type;

        $type = (new ObjectType)
            ->addProperty('data', $items)
            ->setRequired(['data']);

        return self::schema($type, 'Finance employee directory');
    }

    private static function countryProfileResponse(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('code', self::str('BR'))
            ->addProperty('name', self::str('Brazil'))
            ->addProperty('currency', self::str('BRL'))
            ->setRequired(['code', 'name', 'currency']);

        return self::schema($type, 'Supported country/currency profile');
    }

    private static function countryProfileListResponse(): Schema
    {
        $items = new ArrayType;
        $items->items = self::countryProfileResponse()->type;

        $type = (new ObjectType)
            ->addProperty('data', $items)
            ->setRequired(['data']);

        return self::schema($type, 'Employee registration country picker');
    }

    private static function str(string $example, string $description = ''): StringType
    {
        $type = (new StringType)->example($example);
        if ($description !== '') {
            $type->description = $description;
        }

        return $type;
    }

    private static function schema(ObjectType $type, string $description): Schema
    {
        $type->description = $description;

        return Schema::fromType($type);
    }
}
