<?php
namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function all(): Collection
    {
        return Order::with('items.product')->get();
    }

    public function find(int $id): ?Order
    {
        return Order::with('items.product')->find($id);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $order = $this->find($id);
        return $order ? $order->update($data) : false;
    }

    public function delete(int $id): bool
    {
        return Order::destroy($id) > 0;
    }

    public function findByUserId(int $userId): Collection
    {
        return Order::with('items.product')->where('user_id', $userId)->get();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $order = $this->find($id);
        return $order ? $order->update(['status' => $status]) : false;
    }

    public function updatePaymentStatus(int $id, string $paymentStatus): bool
    {
        $order = $this->find($id);
        return $order ? $order->update(['payment_status' => $paymentStatus]) : false;
    }
}
