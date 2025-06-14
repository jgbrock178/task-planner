<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *   title="Task Manager API",
 *   version="1.0.0",
 *   description="Interactive docs for the Task Manager",
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="Token",
 *   description="Enter your personal access token here. You can generate one in your [user settings](/settings/api-tokens).",
 * )
 *
 * @OA\Tag(
 *   name="Tasks",
 *   description="Endpoints to list, create, and toggle tasks"
 * )
 *
 * @OA\Schema(
 *   schema="Task",
 *   type="object",
 *
 *   @OA\Property(property="id",            type="integer", format="int64", example=1),
 *   @OA\Property(property="title",         type="string", example="Buy groceries"),
 *   @OA\Property(property="description",   type="string", nullable=true, example="Milk, Eggs"),
 *   @OA\Property(property="is_completed",  type="boolean", example=false),
 *   @OA\Property(property="priority",      type="string", enum={"high", "medium", "low", "none"}, example="medium"),
 *   @OA\Property(property="due_date",      type="string", format="date", nullable=true, example="2025-06-15"),
 *   @OA\Property(property="created_at",    type="string", format="date-time", example="2025-06-14T12:00:00Z"),
 *   @OA\Property(property="updated_at",    type="string", format="date-time", example="2025-06-14T12:00:00Z"),
 *   @OA\Property(property="completed_at",  type="string", format="date-time", nullable=true, example="2025-06-14T13:00:00Z")
 * )
 */
class TaskController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/tasks",
     *   tags={"Tasks"},
     *   security={{"bearerAuth":{}}},
     *   summary="Get all tasks for the authenticated user",
     *
     *   @OA\Response(
     *     response=200,
     *     description="List of tasks",
     *
     *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Task"))
     *   ),
     *
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $tasks = Task::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($tasks);
    }

    /**
     * @OA\Post(
     *   path="/api/tasks",
     *   tags={"Tasks"},
     *   security={{"bearerAuth":{}}},
     *   summary="Create a new task",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"title"},
     *
     *       @OA\Property(property="title",       type="string", example="Walk the dog"),
     *       @OA\Property(property="description", type="string", example="In the park", nullable=true),
     *       @OA\Property(property="priority",    type="string", enum={"high", "medium", "low", "none"}, example="medium"),
     *       @OA\Property(property="due_date",    type="string", format="date", example="2025-06-15")
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Task created",
     *
     *     @OA\JsonContent(ref="#/components/schemas/Task")
     *   ),
     *
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [
            'title.required' => 'You’ve not entered a task.',
            'title.max' => 'Title can be up to 255 chars.',
        ]);

        $task = Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'user_id' => $request->user()->id,
            'priority' => $request->input('priority', 'none'),
            'due_date' => $request->input('due_date', null),
        ]);

        return response()->json($task, 201);
    }

    /**
     * @OA\Patch(
     *   path="/api/tasks/{task}/toggle-completed",
     *   tags={"Tasks"},
     *   security={{"bearerAuth":{}}},
     *   summary="Toggle a task’s completion status",
     *
     *   @OA\Parameter(
     *     name="task",
     *     in="path",
     *     required=true,
     *
     *     @OA\Schema(type="integer", format="int64")
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Updated task",
     *
     *     @OA\JsonContent(ref="#/components/schemas/Task")
     *   ),
     *
     *   @OA\Response(response=403, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not found"),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function toggleCompleted(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->is_completed
            ? $task->incomplete()
            : $task->complete();

        return response()->json($task);
    }
}
