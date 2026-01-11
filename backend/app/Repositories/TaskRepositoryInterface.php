<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function getAll(int $userId): Collection;

    public function getByDate(int $userId, string $date): Collection;

    public function find(int $id): ?Task;

    public function create(array $data): Task;

    public function update(int $id, array $data): Task;

    public function delete(int $id): bool;

    public function search(int $userId, string $keyword): Collection;

    public function updateSortOrder(array $tasks): bool;
}
