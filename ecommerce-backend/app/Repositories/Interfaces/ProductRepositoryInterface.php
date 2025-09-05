<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Product;

    public function create(array $data): Product;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function where(string $column, $value, string $operator = '='): Collection;

    public function paginate(int $perPage = 40): LengthAwarePaginator;

    // Optional - if implemented in repository
    public function active(): Collection;

    public function exists(int $id): bool;
}