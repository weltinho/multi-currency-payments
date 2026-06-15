<?php

namespace App\Support\Scramble;

use App\OpenApi\ChangePasswordRequest;
use App\OpenApi\CountryProfileListResponse;
use App\OpenApi\CountryProfileResponse;
use App\OpenApi\EmployeeListResponse;
use App\OpenApi\EmployeeResponse;
use App\OpenApi\HealthResponse;
use App\OpenApi\LoginRequest;
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
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\IntegerType;
use Dedoc\Scramble\Support\Generator\Types\NumberType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;

use function DeepCopy\deep_copy;

/**
 * Registers fully populated request/response schemas with example payloads for Scramble.
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
        /** @var array<string, array{request?: array{class: class-string, example: array}, responses?: array<int, array{class: class-string, example: array}>}> $bindings */
        $bindings = [
            'health' => [
                'responses' => [[
                    'status' => 200,
                    'class' => HealthResponse::class,
                    'example' => OpenApiExamples::health(),
                ]],
            ],
            'testUser.index' => [
                'responses' => [[
                    'status' => 200,
                    'class' => TestUsersResponse::class,
                    'example' => OpenApiExamples::testUsers(),
                ]],
            ],
            'auth.login' => [
                'request' => [
                    'class' => LoginRequest::class,
                    'example' => OpenApiExamples::loginRequest(),
                ],
                'responses' => [[
                    'status' => 200,
                    'class' => MessageResponse::class,
                    'example' => OpenApiExamples::messageAuthenticated(),
                ]],
            ],
            'auth.user' => [
                'responses' => [[
                    'status' => 200,
                    'class' => UserResponse::class,
                    'example' => OpenApiExamples::userFinance(),
                ]],
            ],
            'auth.updatePassword' => [
                'request' => [
                    'class' => ChangePasswordRequest::class,
                    'example' => OpenApiExamples::changePasswordRequest(),
                ],
                'responses' => [[
                    'status' => 200,
                    'class' => UserResponse::class,
                    'example' => OpenApiExamples::userEmployee(),
                ]],
            ],
            'employee.index' => [
                'responses' => [[
                    'status' => 200,
                    'class' => EmployeeListResponse::class,
                    'example' => OpenApiExamples::employeeList(),
                ]],
            ],
            'employee.store' => [
                'request' => [
                    'class' => StoreEmployeeRequest::class,
                    'example' => OpenApiExamples::storeEmployeeRequest(),
                ],
                'responses' => [[
                    'status' => 201,
                    'class' => UserResponse::class,
                    'example' => OpenApiExamples::userNewEmployee(),
                ]],
            ],
            'employee.countries' => [
                'responses' => [[
                    'status' => 200,
                    'class' => CountryProfileListResponse::class,
                    'example' => OpenApiExamples::countryProfiles(),
                ]],
            ],
            'payment.index' => [
                'responses' => [[
                    'status' => 200,
                    'class' => PaginatedPaymentResponse::class,
                    'example' => OpenApiExamples::paginatedPayments(),
                ]],
            ],
            'payment.summary' => [
                'responses' => [[
                    'status' => 200,
                    'class' => PaymentSummaryResponse::class,
                    'example' => OpenApiExamples::paymentSummary(),
                ]],
            ],
            'payment.store' => [
                'request' => [
                    'class' => StorePaymentRequest::class,
                    'example' => OpenApiExamples::storePaymentRequest(),
                ],
                'responses' => [[
                    'status' => 201,
                    'class' => PaymentResponse::class,
                    'example' => OpenApiExamples::paymentPending(),
                ]],
            ],
            'payment.show' => [
                'responses' => [[
                    'status' => 200,
                    'class' => PaymentResponse::class,
                    'example' => OpenApiExamples::paymentPending(),
                ]],
            ],
            'payment.decide' => [
                'request' => [
                    'class' => PaymentDecisionRequest::class,
                    'example' => OpenApiExamples::paymentDecisionRequest(),
                ],
                'responses' => [[
                    'status' => 200,
                    'class' => PaymentResponse::class,
                    'example' => OpenApiExamples::paymentApproved(),
                ]],
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

                foreach ($binding['responses'] ?? [] as $responseBinding) {
                    self::bindResponseBody(
                        $document,
                        $operation,
                        $responseBinding['status'],
                        $responseBinding['class'],
                        $responseBinding['example'],
                    );
                }
            }
        }
    }

    /** @param array{class: class-string, example: array} $binding */
    private static function bindRequestBody(OpenApi $document, \Dedoc\Scramble\Support\Generator\Operation $operation, array $binding): void
    {
        $operation->addRequestBodyObject(
            \Dedoc\Scramble\Support\Generator\RequestBodyObject::make()
                ->required(true)
                ->setContent('application/json', self::exampleSchema($document, $binding['class'], $binding['example'])),
        );
    }

    /** @param class-string $schemaClass */
    private static function bindResponseBody(
        OpenApi $document,
        \Dedoc\Scramble\Support\Generator\Operation $operation,
        int $status,
        string $schemaClass,
        array $example,
    ): void {
        foreach ($operation->responses as $index => $response) {
            $resolved = $response instanceof \Dedoc\Scramble\Support\Generator\Reference
                ? $response->resolve()
                : $response;

            if (! $resolved instanceof Response || (int) $resolved->code !== $status) {
                continue;
            }

            $resolved->setContent('application/json', self::exampleSchema($document, $schemaClass, $example));
            $operation->responses[$index] = $resolved;

            return;
        }
    }

    /** @param class-string $schemaClass */
    private static function exampleSchema(OpenApi $document, string $schemaClass, array $example): Schema
    {
        $name = class_basename($schemaClass);
        $schema = deep_copy($document->components->getSchema($name));
        $schema->type->example($example);

        return $schema;
    }

    /** @return array<string, Schema> */
    private static function definitions(): array
    {
        return [
            class_basename(LoginRequest::class) => self::loginRequest(),
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

    private static function loginRequest(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('email', self::str('finance@buzzvel.com')->format('email'))
            ->addProperty('password', self::str('123456'))
            ->setRequired(['email', 'password']);

        return self::schema($type, 'Session login credentials', OpenApiExamples::loginRequest());
    }

    private static function changePasswordRequest(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('current_password', self::str('123456'))
            ->addProperty('password', self::str('654321', 'Six digits — see PasswordPolicy'))
            ->addProperty('password_confirmation', self::str('654321'))
            ->setRequired(['current_password', 'password', 'password_confirmation']);

        return self::schema($type, 'Change password after first login', OpenApiExamples::changePasswordRequest());
    }

    private static function storeEmployeeRequest(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('name', self::str('Jordan Lee'))
            ->addProperty('email', self::str('jordan.lee@buzzvel.com')->format('email'))
            ->addProperty('country_code', self::str('US', 'ISO 3166-1 alpha-2 — sets profile currency'))
            ->setRequired(['name', 'email', 'country_code']);

        return self::schema($type, 'Finance provisions a new employee', OpenApiExamples::storeEmployeeRequest());
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

        return self::schema($type, 'Employee reimbursement request', OpenApiExamples::storePaymentRequest());
    }

    private static function paymentDecisionRequest(): Schema
    {
        $status = self::str('approved', 'Finance decision — approved (green) or rejected (red)');
        $status->enum = ['approved', 'rejected'];

        $type = (new ObjectType)
            ->addProperty('status', $status)
            ->setRequired(['status']);

        return self::schema($type, 'Approve or reject a pending payment', OpenApiExamples::paymentDecisionRequest());
    }

    private static function messageResponse(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('message', self::str('Authenticated'))
            ->setRequired(['message']);

        return self::schema($type, 'Localized success message', OpenApiExamples::messageAuthenticated());
    }

    private static function healthResponse(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('status', self::str('ok'))
            ->setRequired(['status']);

        return self::schema($type, 'Liveness probe', OpenApiExamples::health());
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

        return self::schema($type, 'Authenticated user profile', OpenApiExamples::userFinance());
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
            ->addProperty('reviewed_at', (new StringType)->nullable(true))
            ->addProperty('rate_source', self::str('exchangerate-api.com'))
            ->addProperty('description', self::str('Equipment reimbursement — monitor and peripherals'))
            ->setRequired([
                'id', 'reference', 'user_id', 'user_name', 'country', 'currency',
                'local_amount', 'exchange_rate', 'eur_amount', 'status',
                'created_at', 'updated_at', 'rate_source', 'description',
            ]);

        return self::schema($type, 'Payment request resource', OpenApiExamples::paymentPending());
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
            ->addProperty('total', (new IntegerType)->example(1))
            ->addProperty('from', (new IntegerType)->example(1))
            ->addProperty('to', (new IntegerType)->example(1))
            ->setRequired(['data', 'current_page', 'last_page', 'per_page', 'total', 'from', 'to']);

        return self::schema($type, 'Paginated payment list', OpenApiExamples::paginatedPayments());
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
            ->addProperty('total', (new IntegerType)->example(12))
            ->addProperty('pending', (new IntegerType)->example(4))
            ->addProperty('approved_eur', (new NumberType)->format('float')->example(1842.59))
            ->addProperty('status_counts', $counts)
            ->setRequired(['total', 'pending', 'approved_eur', 'status_counts']);

        return self::schema($type, 'Finance dashboard summary cards', OpenApiExamples::paymentSummary());
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

        return self::schema($type, 'Demo only — grouped evaluator accounts', OpenApiExamples::testUsers());
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

        return self::schema($type, 'Employee list row', OpenApiExamples::employeeList()['data'][0]);
    }

    private static function employeeListResponse(): Schema
    {
        $items = new ArrayType;
        $items->items = self::employeeResponse()->type;

        $type = (new ObjectType)
            ->addProperty('data', $items)
            ->setRequired(['data']);

        return self::schema($type, 'Finance employee directory', OpenApiExamples::employeeList());
    }

    private static function countryProfileResponse(): Schema
    {
        $type = (new ObjectType)
            ->addProperty('code', self::str('BR'))
            ->addProperty('name', self::str('Brazil'))
            ->addProperty('currency', self::str('BRL'))
            ->setRequired(['code', 'name', 'currency']);

        return self::schema($type, 'Supported country/currency profile', OpenApiExamples::countryProfiles()['data'][0]);
    }

    private static function countryProfileListResponse(): Schema
    {
        $items = new ArrayType;
        $items->items = self::countryProfileResponse()->type;

        $type = (new ObjectType)
            ->addProperty('data', $items)
            ->setRequired(['data']);

        return self::schema($type, 'Employee registration country picker', OpenApiExamples::countryProfiles());
    }

    private static function str(string $example, string $description = ''): StringType
    {
        $type = (new StringType)->example($example);
        if ($description !== '') {
            $type->description = $description;
        }

        return $type;
    }

    /** @param array<string, mixed> $example */
    private static function schema(ObjectType $type, string $description, array $example): Schema
    {
        $type->description = $description;
        $type->example($example);

        return Schema::fromType($type);
    }
}
