<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;  // Para resetar o banco de dados a cada teste

    public function test_task_attributes_and_database_insertion()
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        // Criação de uma Task
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Task Description',
            'status' => 'in-progress',
            'created_by' => $user->id,
            'start_date' => now(),
            'end_date' => now()->addDay(),
            'working' => false,
            'time-spent' => 0,
            'workspace_id' => $workspace->id,
        ]);

        // Verificar se os atributos estão corretos
        $this->assertEquals('Test Task', $task->title);
        $this->assertEquals('Task Description', $task->description);
        $this->assertEquals('in-progress', $task->status);

        // Verificar se o banco de dados contém a Task
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'Task Description',
        ]);

        // Verificar relacionamentos
        $this->assertEquals($user->id, $task->createdBy->id);
        $this->assertEquals($workspace->id, $task->workspace->id);
    }

    public function test_task_belongs_to_many_users()
    {
        $users = User::factory(2)->create();
        $task = Task::factory()->create();
        $task->users()->attach($users);

        // Verificar se os usuários estão corretamente associados à Task
        $this->assertCount(2, $task->users);
        $this->assertTrue($task->users->contains($users[0]));
        $this->assertTrue($task->users->contains($users[1]));
    }



    public function test_task_belongs_to_user()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $user->id]);

        // Verificar se o relacionamento com o 'created_by' (User) está correto
        $this->assertInstanceOf(User::class, $task->createdBy);
        $this->assertEquals($user->id, $task->createdBy->id);
    }

    public function test_task_belongs_to_workspace()
    {
        $workspace = Workspace::factory()->create();
        $task = Task::factory()->create(['workspace_id' => $workspace->id]);

        // Verificar se o relacionamento com o 'workspace' está correto
        $this->assertInstanceOf(Workspace::class, $task->workspace);
        $this->assertEquals($workspace->id, $task->workspace->id);
    }
}
