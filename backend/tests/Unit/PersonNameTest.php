<?php

namespace Tests\Unit;

use App\Support\PersonName;
use PHPUnit\Framework\TestCase;

class PersonNameTest extends TestCase
{
    public function test_first_name_returns_first_token(): void
    {
        $this->assertSame('Maria', PersonName::firstName('Maria Silva'));
    }

    public function test_first_name_trims_whitespace(): void
    {
        $this->assertSame('James', PersonName::firstName("  James O'Connor  "));
    }

    public function test_first_name_handles_single_name(): void
    {
        $this->assertSame('Madonna', PersonName::firstName('Madonna'));
    }
}
