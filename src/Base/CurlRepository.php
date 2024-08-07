<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Base;

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

    /**
     * @throws ReflectionException
     */
    public function create(Bean $bean): int
    {
        $model = $this->model->create($bean->toArray());

        return $model->id;
    }

    public function remove(int $id): void
    {
        $this->model->query()->where('id', $id)->delete();
    }

    /**
     * @throws ReflectionException
     */
    public function modify(int $id, Bean $bean): void
    {
        $this->model->query()->where('id', $id)->update($bean->toArray());
    }
}