<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Repositories\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;
    public function __construct(private TaskRepositoryInterface $taskRepository)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        // Get all tasks or filter by date
        if ($request->has('date')) {
            $tasks = $this->taskRepository->getByDate($userId, $request->date);
        } else {
            $tasks = $this->taskRepository->getAll($userId);
        }

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): TaskResource
    {
        $this->authorize('create', Task::class);

        $data = array_merge($request->validated(), [
            'user_id' => $request->user()->id,
        ]);

        $task = $this->taskRepository->create($data);

        return new TaskResource($task);
    }

    public function show(int $id): TaskResource
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            abort(404, 'Task not found');
        }

        // Check if user owns this task
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, int $id): TaskResource
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            abort(404, 'Task not found');
        }

        // Check if user owns this task
        $this->authorize('update', $task);

        $updatedTask = $this->taskRepository->update($id, $request->validated());

        return new TaskResource($updatedTask);
    }

    public function destroy(int $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            abort(404, 'Task not found');
        }

        // Check if user owns this task
        $this->authorize('delete', $task);

        $this->taskRepository->delete($id);

        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'keyword' => 'required|string|min:1',
        ]);

        $tasks = $this->taskRepository->search($request->user()->id, $request->keyword);

        return TaskResource::collection($tasks);
    }

    public function reorder(ReorderTasksRequest $request): JsonResponse
    {
        // Check if user owns all tasks
        foreach ($request->validated()['tasks'] as $taskData) {
            $task = $this->taskRepository->find($taskData['id']);
            $this->authorize('update', $task);
        }

        $this->taskRepository->updateSortOrder($request->validated()['tasks']);

        return response()->json(['message' => 'Tasks reordered successfully']);
    }
}
