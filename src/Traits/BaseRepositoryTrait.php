<?php
// src/Traits/BaseRepositoryTrait.php

namespace VotreNom\SmartCrud\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

trait BaseRepositoryTrait
{
    /**
     * Find model by multiple conditions
     */
    public function findWhere(array $conditions): Collection
    {
        $query = $this->model->newQuery();
        
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->get();
    }

    /**
     * Find first model by conditions
     */
    public function findFirstWhere(array $conditions): ?Model
    {
        $query = $this->model->newQuery();
        
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->first();
    }

    /**
     * Count records by conditions
     */
    public function countWhere(array $conditions): int
    {
        $query = $this->model->newQuery();
        
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->count();
    }

    /**
     * Search in multiple fields
     */
    public function search(string $query, array $fields): Collection
    {
        $queryBuilder = $this->model->newQuery();
        
        $queryBuilder->where(function ($q) use ($query, $fields) {
            foreach ($fields as $field) {
                $q->orWhere($field, 'like', "%{$query}%");
            }
        });
        
        return $queryBuilder->get();
    }

    /**
     * Get latest records
     */
    public function latest(int $limit = 10): Collection
    {
        return $this->model->latest()->limit($limit)->get();
    }

    /**
     * Get oldest records
     */
    public function oldest(int $limit = 10): Collection
    {
        return $this->model->oldest()->limit($limit)->get();
    }

    /**
     * Bulk insert with timestamps
     */
    public function bulkInsert(array $data): bool
    {
        $now = Carbon::now();
        
        $dataWithTimestamps = collect($data)->map(function ($item) use ($now) {
            return array_merge($item, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        })->toArray();
        
        return $this->model->insert($dataWithTimestamps);
    }

    /**
     * Get random records
     */
    public function random(int $limit = 5): Collection
    {
        return $this->model->inRandomOrder()->limit($limit)->get();
    }

    /**
     * Paginate with custom query
     */
    public function paginateWhere(array $conditions, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();
        
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Get distinct values for a column
     */
    public function getDistinct(string $column): Collection
    {
        return $this->model->distinct()->pluck($column);
    }

    /**
     * Get records within date range
     */
    public function getByDateRange(string $dateField, string $startDate, string $endDate): Collection
    {
        return $this->model
            ->whereBetween($dateField, [$startDate, $endDate])
            ->get();
    }

    /**
     * Get records with relationships
     */
    public function getWithRelations(array $relations): Collection
    {
        return $this->model->with($relations)->get();
    }

    /**
     * Apply scopes dynamically
     */
    public function applyScopes(array $scopes): Builder
    {
        $query = $this->model->newQuery();
        
        foreach ($scopes as $scope => $parameters) {
            if (is_numeric($scope)) {
                // Simple scope without parameters
                $query->{$parameters}();
            } else {
                // Scope with parameters
                $query->{$scope}($parameters);
            }
        }
        
        return $query;
    }

    /**
     * Update multiple records by conditions
     */
    public function updateWhere(array $conditions, array $data): int
    {
        $query = $this->model->newQuery();
        
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->update($data);
    }

    /**
     * Delete records by conditions
     */
    public function deleteWhere(array $conditions): int
    {
        $query = $this->model->newQuery();
        
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->delete();
    }

    /**
     * Check if record exists by conditions
     */
    public function existsWhere(array $conditions): bool
    {
        $query = $this->model->newQuery();
        
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->exists();
    }
}