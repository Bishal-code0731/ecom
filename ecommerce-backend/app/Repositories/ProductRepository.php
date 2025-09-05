<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function all(): Collection
    {
        return Product::all();
    }

    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $product = $this->find($id);
        return $product ? $product->update($data) : false;
    }

    public function delete(int $id): bool
    {
        return Product::destroy($id) > 0;
    }

    /**
     * Fetch products filtered by column with optional operator.
     *
     * @param string $column
     * @param mixed $value
     * @param string $operator
     * @return Collection
     */
    public function where(string $column, $value, string $operator = '='): Collection
    {
        return Product::where($column, $operator, $value)->get();
    }

    public function paginate(int $perPage = 40): LengthAwarePaginator
    {
        return Product::paginate($perPage);
    }

    /**
     * Optional: fetch only active products if you keep this method.
     */
    public function active(): Collection
    {
        return Product::active()->get();
    }

    /**
     * Optional: check if product exists.
     */
    public function exists(int $id): bool
    {
        return Product::where('id', $id)->exists();
    }
}