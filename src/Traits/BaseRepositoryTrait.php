<?php

namespace Rouangni\SmartCrud\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

trait BaseRepositoryTrait
{
    /**
     * Get all records with optional filtering
     */
    public function all(array $filters = [], array $relations = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get paginated records
     */
    public function paginate(
        int $perPage = 15, 
        array $filters = [], 
        array $relations = []
    ): LengthAwarePaginator {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Find record by ID
     */
    public function find(int $id, array $relations = []): ?Model
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Find record by ID or fail
     */
    public function findOrFail(int $id, array $relations = []): Model
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->findOrFail($id);
    }

    /**
     * Create a new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->fresh();
    }

    /**
     * Delete a record
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Find records by specific field
     */
    public function findBy(string $field, $value, array $relations = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->where($field, $value)->get();
    }

    /**
     * Find first record by specific field
     */
    public function findOneBy(string $field, $value, array $relations = []): ?Model
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->where($field, $value)->first();
    }

    /**
     * Count records with optional filtering
     */
    public function count(array $filters = []): int
    {
        $query = $this->model->newQuery();
        $this->applyFilters($query, $filters);
        
        return $query->count();
    }

    /**
     * Check if record exists
     */
    public function exists(array $criteria): bool
    {
        return $this->model->where($criteria)->exists();
    }

    /**
     * Get first record or create
     */
    public function firstOrCreate(array $criteria, array $data = []): Model
    {
        return $this->model->firstOrCreate($criteria, $data);
    }

    /**
     * Update or create record
     */
    public function updateOrCreate(array $criteria, array $data): Model
    {
        return $this->model->updateOrCreate($criteria, $data);
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if (is_null($value)) {
                continue;
            }

            // Handle array values for IN queries
            if (is_array($value)) {
                $query->whereIn($field, $value);
                continue;
            }

            // Handle range filters (field_from, field_to)
            if (str_ends_with($field, '_from')) {
                $actualField = str_replace('_from', '', $field);
                $query->where($actualField, '>=', $value);
                continue;
            }

            if (str_ends_with($field, '_to')) {
                $actualField = str_replace('_to', '', $field);
                $query->where($actualField, '<=', $value);
                continue;
            }

            // Handle LIKE searches
            if (str_ends_with($field, '_like')) {
                $actualField = str_replace('_like', '', $field);
                $query->where($actualField, 'LIKE', "%{$value}%");
                continue;
            }

            // Default exact match
            $query->where($field, $value);
        }
    }

    /**
     * Apply sorting to query
     */
    protected function applySorting(Builder $query, ?string $sortBy = null, string $sortDirection = 'asc'): void
    {
        if ($sortBy && $this->isValidSortField($sortBy)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            // Default sorting
            $query->latest();
        }
    }

    /**
     * Check if field is valid for sorting
     */
    protected function isValidSortField(string $field): bool
    {
        // Override this method in your repositories to define allowed sort fields
        return in_array($field, $this->getSortableFields());
    }

    /**
     * Get sortable fields - should be overridden in repositories
     */
    protected function getSortableFields(): array
    {
        return ['id', 'created_at', 'updated_at'];
    }
}