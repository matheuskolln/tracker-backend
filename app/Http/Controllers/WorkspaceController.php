<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    public function index()
    {
        $workspaces = Auth::user()->workspaces;
        return response()->json($workspaces);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workspace = Workspace::create([
            'name' => $request->name,
            'owner_id' => Auth::id(),
        ]);


        $workspace->users()->attach(Auth::id());

        return response()->json($workspace, 201);
    }

    public function show($id)
    {
        $workspace = Workspace::with('tasks')->findOrFail($id);
        // Mostrar usuarios das tarefas
        $workspace->tasks->load('users');

        if (!$workspace->users->contains(Auth::id())) {
            return response()->json(['error' => 'Você não tem permissão para acessar este workspace.'], 403);
        }

        return response()->json($workspace);
    }

    public function addUser(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $workspace = Workspace::findOrFail($id);
        $user = User::findOrFail($request->user_id);

        if ($workspace->users->contains($user)) {
            return response()->json(['error' => 'Usuário já está no workspace.'], 400);
        }


        if ($workspace->owner_id != Auth::id()) {
            return response()->json(['error' => 'Você não é o dono deste workspace.'], 403);
        }

        $workspace->users()->attach($user);

        return response()->json(['message' => 'Usuário adicionado ao workspace.'], 200);
    }

    public function removeUser(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $workspace = Workspace::findOrFail($id);
        $user = User::findOrFail($request->user_id);

        if ($workspace->owner_id == $user->id) {
            return response()->json(['error' => 'Você não pode se remover do workspace.'], 400);
        }


        if ($workspace->owner_id != Auth::id()) {
            return response()->json(['error' => 'Você não é o dono deste workspace.'], 403);
        }

        $workspace->users()->detach($user);

        return response()->json(['message' => 'Usuário removido do workspace.'], 200);
    }
}
