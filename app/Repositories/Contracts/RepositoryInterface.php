<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Get all records
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find a record by ID
     *
     * @param int $id
     * @param array $columns
     * @return Model|null
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find a record by ID or fail
     *
     * @param int $id
     * @param array $columns
     * @return Model
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Create a new record
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update a record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Eager load relationships
     *
     * @param array|string $relations
     * @return self
     */
    public function with($relations): self;

    /**
     * Paginate results
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find records by a field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return Collection
     */
    public function findBy(string $field, $value, array $columns = ['*']): Collection;

    /**
     * Find first record by a field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return Model|null
     */
    public function findOneBy(string $field, $value, array $columns = ['*']): ?Model;
}
