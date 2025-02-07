<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Auth::user()->tasks()->orderBy('created_at', 'desc')->get();
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        $task = Auth::user()->tasks()->create(array_merge($validatedData, ['status' => 'To Do']));

        return response()->json($task, 201);
    }


    public function show($id)
    {
        $task = Auth::user()->tasks()->findOrFail($id);
        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Auth::user()->tasks()->findOrFail($id);

        $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status'      => 'sometimes|in:To Do,In Progress,Done',
            'start_date'  => 'sometimes|date|nullable',
            'end_date'    => [
                'sometimes',
                'date',
                'nullable',
                function ($attribute, $value, $fail) use ($task) {
                    $startDate = $task->start_date ?? request('start_date');
                    if ($startDate && $value < $startDate) {
                        $fail('The end date must be after or equal to the start date.');
                    }
                },
            ],
        ]);

        $task->fill($request->all())->save();

        return response()->json($task);
    }


    public function destroy($id)
    {
        $task = Auth::user()->tasks()->findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
