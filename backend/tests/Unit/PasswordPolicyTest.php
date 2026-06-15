<?php

namespace Tests\Unit;

use App\Support\PasswordPolicy;
use Tests\TestCase;

/** Keeps the demo password rules honest — change these if PasswordPolicy changes. */
class PasswordPolicyTest extends TestCase
{
    public function test_it_accepts_six_digit_passwords(): void
    {
        $this->assertTrue(PasswordPolicy::isValid('123456'));
        $this->assertTrue(PasswordPolicy::isValid('000000'));
    }

    public function test_it_rejects_non_digit_or_wrong_length_passwords(): void
    {
        $this->assertFalse(PasswordPolicy::isValid('12345'));
        $this->assertFalse(PasswordPolicy::isValid('1234567'));
        $this->assertFalse(PasswordPolicy::isValid('12ab56'));
        $this->assertFalse(PasswordPolicy::isValid('Maria Silva'));
    }
}
