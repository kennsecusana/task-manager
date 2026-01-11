<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Support\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function getAll(int $userId): Collection
    {
        return Task::where('user_id', $userId)
            ->orderBy('task_date', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    public function getByDate(int $userId, string $date): Collection
    {
        return Task::where('user_id', $userId)
            ->whereDate('task_date', $date)
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    public function find(int $id): ?Task
    {
        return Task::find($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(int $id, array $data): Task
    {
        $task = Task::findOrFail($id);
        $task->update($data);

        return $task->fresh();
    }

    public function delete(int $id): bool
    {
        $task = Task::findOrFail($id);

        return $task->delete();
    }

    public function search(int $userId, string $keyword): Collection
    {
        return Task::where('user_id', $userId)
            ->where('statement', 'like', "%{$keyword}%")
            ->orderBy('task_date', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    public function updateSortOrder(array $tasks): bool
    {
        foreach ($tasks as $taskData) {
            Task::where('id', $taskData['id'])
                ->update(['sort_order' => $taskData['sort_order']]);
        }

        return true;
    }
}
