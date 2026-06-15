<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $this->assertSame(
            'payments_test',
            config('database.connections.mysql.database'),
            'PHPUnit must use the isolated test database, not demo data.',
        );

        $response = $this->getJson('/api/health');

        $response->assertOk()
            ->assertJson(['status' => 'ok']);
    }
}
