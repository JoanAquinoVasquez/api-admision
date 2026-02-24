<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * Relations to eager load
     *
     * @var array
     */
    protected array $with = [];

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->model = $this->makeModel();
    }

    /**
     * Create model instance
     *
     * @return Model
     */
    abstract protected function makeModel(): Model;

    /**
     * Get all records
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->with($this->with)->get($columns);
    }

    /**
     * Find a record by ID
     *
     * @param int $id
     * @param array $columns
     * @return Model|null
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->with($this->with)->find($id, $columns);
    }

    /**
     * Find a record by ID or fail
     *
     * @param int $id
     * @param array $columns
     * @return Model
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->with($this->with)->findOrFail($id, $columns);
    }

    /**
     * Create a new record
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $record = $this->findOrFail($id);
        return $record->update($data);
    }

    /**
     * Delete a record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }

    /**
     * Eager load relationships
     *
     * @param array|string $relations
     * @return self
     */
    public function with($relations): self
    {
        $this->with = is_array($relations) ? $relations : func_get_args();
        return $this;
    }

    /**
     * Paginate results
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->with($this->with)->paginate($perPage, $columns);
    }

    /**
     * Find records by a field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return Collection
     */
    public function findBy(string $field, $value, array $columns = ['*']): Collection
    {
        return $this->model->with($this->with)->where($field, $value)->get($columns);
    }

    /**
     * Find first record by a field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return Model|null
     */
    public function findOneBy(string $field, $value, array $columns = ['*']): ?Model
    {
        return $this->model->with($this->with)->where($field, $value)->first($columns);
    }

    /**
     * Reset eager loading relationships
     *
     * @return self
     */
    protected function resetWith(): self
    {
        $this->with = [];
        return $this;
    }
}
