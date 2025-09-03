<?php

namespace App\Repositories;

use App\Models\Payment; // Assuming you have a Payment model

class PaymentRepository
{
    /**
     * Create a new payment record.
     *
     * @param array $data
     * @return Payment
     */
    public function create(array $data)
    {
        return Payment::create($data);
    }

    /**
     * Find a payment by its ID.
     *
     * @param int $id
     * @return Payment|null
     */
    public function find(int $id)
    {
        return Payment::find($id);
    }

    /**
     * Update payment record.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $payment = $this->find($id);
        return $payment ? $payment->update($data) : false;
    }
}
