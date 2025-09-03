<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Order;

    public function create(array $data): Order;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function findByUserId(int $userId): Collection;

    public function updateStatus(int $id, string $status): bool;

    public function updatePaymentStatus(int $id, string $paymentStatus): bool;
}
