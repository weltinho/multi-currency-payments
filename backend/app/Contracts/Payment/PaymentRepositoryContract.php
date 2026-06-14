<?php

namespace App\Contracts\Payment;

interface PaymentRepositoryContract
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function filter(array $filters): array;

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function updateStatus(string $id, string $status): ?array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(array $data): array;
}
