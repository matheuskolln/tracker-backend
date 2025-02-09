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

        $user_id = Auth::id();
        $task = new Task($validatedData);
        $task->created_by = $user_id;
        $task->save();


        return response()->json($task, 201);
    }


    public function show($id)
    {
        $task = Task::findOrFail($id);
        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);


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
            'working'     => 'sometimes|boolean',
            'time_spent'  => 'sometimes|integer',
            'user_ids'    => 'sometimes|array',
            'user_ids.*'  => 'exists:users,id', // Cada ID deve existir na tabela `users`
        ]);

        $task->fill($request->except('user_ids'))->save();

        if ($request->has('user_ids')) {
            $task->users()->sync($request->user_ids);
        }

        return response()->json($task->load('users'));
    }



    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
