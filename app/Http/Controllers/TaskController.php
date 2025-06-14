<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $priority = $request->query('priority');
        $due = $request->query('due');

        $tasks = Task::where('user_id', Auth::id())
            ->when($priority, fn ($q) => $q->where('priority', $priority))
            ->when($due === 'today', fn ($q) => $q->whereDate('due_date', today()))
            ->when($due === 'thisweek', fn ($q) => $q->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($due === 'thismonth', fn ($q) => $q->whereBetween('due_date', [now()->startOfMonth(), now()->endOfMonth()]))
            ->when($due === 'overdue', fn ($q) => $q
                ->whereDate('due_date', '<', today())
                ->where('completed_at', null)
            )
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Tasks', [
            'tasks' => $tasks,
            'priority' => $priority,
            'due' => $due,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [
            'title.required' => 'Oops. The task needs a title.',
            'title.max' => 'Your title is too long. It can only be up to 255 characters.',
        ]);

        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => Auth::id(),
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [
            'title.required' => 'Oops. The task needs a title.',
            'title.max' => 'Your title is too long. It can only be up to 255 characters.',
        ]);

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function toggleCompleted(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($task->is_completed) {
            $task->incomplete();
        } else {
            $task->complete();
        }

        return redirect()->route('tasks.index')->with('success', 'Task marked as completed.');
    }

    public function reorder(Request $request)
    {
        $userId = $request->user()->id;
        $request->validate([
            'order' => 'required|array',
            'order.*' => "required|integer|exists:tasks,id,user_id,{$userId}",
        ]);

        $userId = $request->user()->id;
        $ids = $request->order;

        DB::transaction(function () use ($ids, $userId) {
            foreach ($ids as $position => $id) {
                Task::where('id', $id)
                    ->where('user_id', $userId)
                    ->update(['sort_order' => $position]);
            }
        });

        return redirect()->route('tasks.index')->with('success', 'Tasks order saved.');
    }
}
