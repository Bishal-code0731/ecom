<?php

namespace App\Repositories\Interfaces;

interface PaymentRepositoryInterface
{
    /**
     * Create a new payment record.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Find a payment by its ID.
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * Update a payment record.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;
}
