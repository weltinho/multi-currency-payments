<?php

namespace Tests\Unit;

use App\Contracts\ExchangeRate\ExchangeRateServiceContract;
use App\Contracts\Payment\PaymentRepositoryContract;
use App\Enums\UserRole;
use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use App\Services\Payment\PaymentService;
use Mockery;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_employee_cannot_decide_payment(): void
    {
        $repository = Mockery::mock(PaymentRepositoryContract::class);
        $repository->shouldNotReceive('find');

        $service = new PaymentService($repository, $this->exchangeRates());
        $employee = $this->makeUser(UserRole::Employee);

        $this->expectException(ForbiddenException::class);
        $service->decide($employee, '1', 'approved');
    }

    public function test_finance_cannot_decide_missing_payment(): void
    {
        $repository = Mockery::mock(PaymentRepositoryContract::class);
        $repository->shouldReceive('find')->once()->with('missing')->andReturn(null);

        $service = new PaymentService($repository, $this->exchangeRates());
        $finance = $this->makeUser(UserRole::Finance);

        $this->expectException(NotFoundException::class);
        $service->decide($finance, 'missing', 'approved');
    }

    public function test_finance_cannot_decide_non_pending_payment(): void
    {
        $repository = Mockery::mock(PaymentRepositoryContract::class);
        $repository->shouldReceive('find')->once()->with('2')->andReturn([
            'id' => 2,
            'status' => 'approved',
        ]);

        $service = new PaymentService($repository, $this->exchangeRates());
        $finance = $this->makeUser(UserRole::Finance);

        $this->expectException(ConflictException::class);
        $service->decide($finance, '2', 'rejected');
    }

    public function test_finance_can_approve_pending_payment(): void
    {
        $pending = [
            'id' => 1,
            'status' => 'pending',
        ];
        $approved = [
            'id' => 1,
            'status' => 'approved',
        ];

        $repository = Mockery::mock(PaymentRepositoryContract::class);
        $repository->shouldReceive('find')->once()->with('1')->andReturn($pending);
        $repository->shouldReceive('updateStatus')->once()->with('1', 'approved')->andReturn($approved);

        $service = new PaymentService($repository, $this->exchangeRates());
        $finance = $this->makeUser(UserRole::Finance);

        $this->assertSame($approved, $service->decide($finance, '1', 'approved'));
    }

    public function test_employee_pagination_scopes_to_own_user_id(): void
    {
        $repository = Mockery::mock(PaymentRepositoryContract::class);
        $repository->shouldReceive('filter')
            ->once()
            ->with(Mockery::on(function (array $filters) {
                return $filters['user_id'] === '42' && $filters['status'] === null;
            }))
            ->andReturn([]);

        $service = new PaymentService($repository, $this->exchangeRates());
        $employee = $this->makeUser(UserRole::Employee, 42);

        $result = $service->paginate($employee, ['page' => 1, 'per_page' => 8]);

        $this->assertSame(0, $result['total']);
        $this->assertSame([], $result['data']);
    }

    public function test_employee_can_create_payment(): void
    {
        $employee = $this->makeUser(UserRole::Employee, 5);
        $employee->currency = 'BRL';

        $exchangeRates = Mockery::mock(ExchangeRateServiceContract::class);
        $exchangeRates->shouldReceive('getRateForCurrency')
            ->once()
            ->with('BRL')
            ->andReturn([
                'rate' => 6.21,
                'source' => 'exchangerate-api.com',
                'fetched_at' => now(),
            ]);

        $created = ['id' => 10, 'status' => 'pending', 'eur_amount' => 676.33];

        $repository = Mockery::mock(PaymentRepositoryContract::class);
        $repository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['user_id'] === 5
                    && $data['currency'] === 'BRL'
                    && $data['local_amount'] === 4200.0
                    && $data['exchange_rate'] === 6.21
                    && $data['eur_amount'] === 676.33;
            }))
            ->andReturn($created);

        $service = new PaymentService($repository, $exchangeRates);

        $this->assertSame(
            $created,
            $service->create($employee, ['description' => 'Test', 'local_amount' => 4200]),
        );
    }

    public function test_employee_can_create_payment_with_explicit_currency(): void
    {
        $employee = $this->makeUser(UserRole::Employee, 5);
        $employee->currency = 'BRL';

        $exchangeRates = Mockery::mock(ExchangeRateServiceContract::class);
        $exchangeRates->shouldReceive('getRateForCurrency')
            ->once()
            ->with('USD')
            ->andReturn([
                'rate' => 1.08,
                'source' => 'exchangerate-api.com',
                'fetched_at' => now(),
            ]);

        $created = ['id' => 11, 'status' => 'pending', 'currency' => 'USD', 'eur_amount' => 100.0];

        $repository = Mockery::mock(PaymentRepositoryContract::class);
        $repository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['user_id'] === 5
                    && $data['currency'] === 'USD'
                    && $data['local_amount'] === 108.0
                    && $data['exchange_rate'] === 1.08
                    && $data['eur_amount'] === 100.0;
            }))
            ->andReturn($created);

        $service = new PaymentService($repository, $exchangeRates);

        $this->assertSame(
            $created,
            $service->create($employee, [
                'description' => 'USD expense',
                'local_amount' => 108,
                'currency' => 'USD',
            ]),
        );
    }

    public function test_expire_stale_pending_delegates_to_repository(): void
    {
        config(['payments.pending_expiration_hours' => 48]);

        $repository = Mockery::mock(PaymentRepositoryContract::class);
        $repository->shouldReceive('expirePendingOlderThan')
            ->once()
            ->with(Mockery::on(function (\DateTimeInterface $cutoff) {
                $expected = now()->subHours(48);

                return abs($cutoff->getTimestamp() - $expected->getTimestamp()) < 2;
            }))
            ->andReturn(3);

        $service = new PaymentService($repository, $this->exchangeRates());

        $this->assertSame(3, $service->expireStalePending());
    }

    private function exchangeRates(): ExchangeRateServiceContract
    {
        return Mockery::mock(ExchangeRateServiceContract::class);
    }

    private function makeUser(UserRole $role, int $id = 1): User
    {
        $user = new User;
        $user->id = $id;
        $user->role = $role;

        return $user;
    }
}
