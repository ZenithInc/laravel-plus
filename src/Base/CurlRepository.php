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

    protected string $primaryKey = 'id';

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->primaryKey = $this->model->getKeyName();
    }

    public function findById(int|string $id): Optional
    {
        return Optional::ofNullable($this->model->query()->where($this->primaryKey, $id)->first());
    }

    public function existsById(int|string $id): bool
    {
        return $this->model->query()->where($this->primaryKey, $id)->exists();
    }

    public function existsInIds(array $ids): bool
    {
        $records = $this->model->whereIn($this->primaryKey, $ids)->get([$this->primaryKey]);

        return count($records) === count($ids);
    }

    /**
     * @throws ReflectionException
     */
    public function create(Bean $bean): int|string
    {
        $model = $this->model->create($bean->toArray());

        return $model->id;
    }

    public function createWithArray(array $data): int|string
    {
        $model = $this->model->create($data);

        return $model->id;
    }

    public function batchCreate(array $records): void
    {
        $this->model->insert($records);
    }

    public function remove(int|string $id): void
    {
        $this->model->query()->where($this->primaryKey, $id)->delete();
    }

    /**
     * @throws ReflectionException
     */
    public function modify(int|string $id, Bean $bean, array $excludes = []): void
    {
        $data = collect($bean->toArray())->filter(fn($value, $key) => ! in_array($key, $excludes))->toArray();
        $this->model->query()->where($this->primaryKey, $id)->update($data);
    }

    public function modifyWithArray(int|string $id, array $params, array $excludes = []): void
    {
        if (isset($params[$this->primaryKey])) {
            unset($params[$this->primaryKey]);
        }
        $data = collect($params)->filter(fn($value, $key) => ! in_array($key, $excludes))->toArray();
        $this->model->query()->where($this->primaryKey, $id)->update($data);
    }

    public function findAll(): Collection
    {
        return $this->model->query()->get();
    }

    public function findByIds(array $ids): array
    {
        return $this->model->query()->whereIn($this->primaryKey, $ids)->get()->toArray();
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