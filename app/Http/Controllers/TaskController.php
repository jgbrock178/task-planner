<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::where('user_id', Auth::id())
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Tasks', [
            'tasks' => $tasks,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [
            'title.required' => 'You\'ve not entered a task.',
            'title.max' => 'You\'re title is too long. It can only be up to 255 characters.',
        ]);

        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
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
