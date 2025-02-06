<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskTimeController extends Controller
{
    public function index (Task $task)
    {
        if ($task->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $taskTimes = TaskTime::where('task_id', $task->id)
                            ->where('user_id', Auth::id())
                            ->get();

        return response()->json($taskTimes);
    }

    public function startTimer(Task $task)
    {
        if ($task->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        # Check if there is an active timer for the task
        $activeTimer = TaskTime::where('task_id', $task->id)
                                ->where('user_id', Auth::id())
                                ->where('status', 'started')
                                ->first();

        if ($activeTimer) {
            return response()->json(['message' => 'Timer already started'], 400);
        }

        $taskTime = TaskTime::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'time_spent' => 0,
            'status' => 'started',
            'start_time' => now(),
            'end_time' => null
        ]);

        return response()->json($taskTime, 201);
    }

    public function stopTimer(Task $task)
    {
        if ($task->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $taskTime = TaskTime::where('task_id', $task->id)
                            ->where('user_id', Auth::id())
                            ->where('status', 'started')
                            ->latest()
                            ->first();

        if ($taskTime) {
            $taskTime->end_time = now();

            $startTime = Carbon::parse($taskTime->start_time);

            $taskTime->time_spent = $startTime->diffInMinutes($taskTime->end_time);
            $taskTime->status = 'stopped';
            $taskTime->save();

            return response()->json($taskTime);
        }

        return response()->json(['message' => 'No active timer found'], 404);
    }

    public function manualTimeEntry(Request $request, Task $task)
    {
        $request->validate([
            'time_spent' => 'required|integer|min:1',
        ]);

        if ($task->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $taskTime = TaskTime::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'start_time' => now(),
            'end_time' => null,
            'time_spent' => $request->time_spent,
            'status' => 'manual'
        ]);

        return response()->json($taskTime, 201);
    }
}
