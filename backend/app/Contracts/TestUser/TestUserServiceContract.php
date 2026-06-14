<?php

namespace App\Contracts\TestUser;

interface TestUserServiceContract
{
    /**
     * @return array{finance: array<int, array<string, string>>, employees: array<int, array<string, string>>}
     */
    public function listGroupedByRole(): array;
}
