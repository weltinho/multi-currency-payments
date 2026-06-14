<?php

namespace App\Contracts\Payment;

use App\Models\User;

interface PaymentServiceContract
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function paginate(User $user, array $query): array;

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function summary(User $user, array $query): array;

    /**
     * @return array<string, mixed>
     */
    public function decide(User $user, string $id, string $status): array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $user, array $data): array;

    /**
     * @return array<string, mixed>
     */
    public function show(User $user, string $id): array;
}
