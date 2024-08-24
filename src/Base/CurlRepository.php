<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Base;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ReflectionException;
use Zenith\LaravelPlus\Bean;
use Zenith\LaravelPlus\Optional;

/**
 * @template T extends Illuminate\Database\Eloquent\Model
 */
class CurlRepository
{
    /**
     * @template T
     */
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): Optional
    {
        return Optional::ofNullable($this->model->query()->where('id', $id)->first());
    }

    public function existsById(int $id): bool
    {
        return $this->model->query()->where('id', $id)->exists();
    }

    public function existsInIds(array $ids): bool
    {
        $records = $this->model->whereIn('id', $ids)->get(['id']);

        return count($records) === count($ids);
    }

    /**
     * @throws ReflectionException
     */
    public function create(Bean $bean): int
    {
        $model = $this->model->create($bean->toArray());

        return $model->id;
    }

    public function createWithArray(array $data): int
    {
        $model = $this->model->create($data);

        return $model->id;
    }

    public function batchCreate(array $records): void
    {
        $this->model->insert($records);
    }

    public function remove(int $id): void
    {
        $this->model->query()->where('id', $id)->delete();
    }

    /**
     * @throws ReflectionException
     */
    public function modify(int $id, Bean $bean, array $excludes = []): void
    {
        $data = collect($bean->toArray())->filter(fn($value, $key) => ! in_array($key, $excludes))->toArray();
        $this->model->query()->where('id', $id)->update($data);
    }

    public function modifyWithArray(int $id, array $params, array $excludes = []): void
    {
        $data = collect($params)->filter(fn($value, $key) => ! in_array($key, $excludes))->toArray();
        $this->model->query()->where('id', $id)->update($data);
    }

    public function findAll(): Collection
    {
        return $this->model->query()->get();
    }

    public function findByIds(array $ids): array
    {
        return $this->model->query()->whereIn('id', $ids)->get()->toArray();
    }

    public function existsByFields(array $conditions): bool
    {
        $query = $this->model->query();
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        return $query->exists();
    }
}