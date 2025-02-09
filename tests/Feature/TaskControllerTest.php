<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $workspace;
    protected $task;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and mark email as verified
        $this->user = User::factory()->create();
        $this->user->markEmailAsVerified(); // Mark email as verified

        // Generate a token for the user
        $this->token = $this->user->createToken('TestToken')->plainTextToken;

        // Create a workspace and associate the user
        $this->workspace = Workspace::factory()->create();
        $this->workspace->users()->attach($this->user);

        // Create a task within the workspace
        $this->task = Task::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_user_can_list_tasks()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/tasks?workspace_id=' . $this->workspace->id);

        // Check if the response status is 200
        $response->assertStatus(200);

        // Check if the response has the expected JSON structure
        $response->assertJsonStructure([
            '*' => ['id', 'title', 'description', 'workspace_id', 'created_by'],
        ]);
    }

    public function test_user_cannot_list_tasks_of_other_workspace()
    {
        $otherWorkspace = Workspace::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/tasks?workspace_id=' . $otherWorkspace->id);

        $response->assertStatus(403);
    }

    public function test_user_can_create_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'workspace_id' => $this->workspace->id,
        ]);

        $response->assertStatus(201)->assertJson([
            'title' => 'New Task',
            'description' => 'Task description',
            'workspace_id' => $this->workspace->id,
        ]);
    }

    public function test_user_cannot_create_task_in_other_workspace()
    {
        $otherWorkspace = Workspace::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tasks', [
            'title' => 'Unauthorized Task',
            'description' => 'Unauthorized description',
            'workspace_id' => $otherWorkspace->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_view_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/tasks/' . $this->task->id);

        $response->assertStatus(200)->assertJson([
            'id' => $this->task->id,
            'title' => $this->task->title,
            'description' => $this->task->description,
        ]);
    }

    public function test_user_cannot_view_task_in_other_workspace()
    {
        $otherWorkspace = Workspace::factory()->create();
        $otherTask = Task::factory()->create(['workspace_id' => $otherWorkspace->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/tasks/' . $otherTask->id);

        $response->assertStatus(403);
    }

    public function test_user_can_update_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/tasks/' . $this->task->id, [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)->assertJson([
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);
    }

    public function test_when_update_task_users_should_add_to_task()
    {
        $user2 = User::factory()->create();
        $this->workspace->users()->attach($user2);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/tasks/' . $this->task->id, [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'user_ids' => [$this->user->id, $user2->id],
        ]);

        $response->assertStatus(200)->assertJson([
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

       $this->assertContains($this->user->id, $this->task->users->pluck('id'));

    }

    public function test_user_cannot_update_task_in_other_workspace()
    {
        $otherWorkspace = Workspace::factory()->create();
        $otherTask = Task::factory()->create(['workspace_id' => $otherWorkspace->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/tasks/' . $otherTask->id, [
            'title' => 'Hacked',
            'description' => 'Hacked description',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_task_with_invalid_end_date()
    {
        // Create a task with a start_date
        $task = Task::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->user->id,
            'start_date' => '2023-10-01',
        ]);

        // Attempt to update the task with an end_date before the start_date
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/tasks/' . $task->id, [
            'end_date' => '2023-09-30', // Invalid end_date
        ]);

        // Assert that the response contains the validation error
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['end_date']);
    }

    public function test_user_can_delete_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/tasks/' . $this->task->id);

        $response->assertStatus(200)->assertJson([
            'message' => 'Task deleted successfully',
        ]);

        // Ensure the task is deleted from the database
        $this->assertDatabaseMissing('tasks', ['id' => $this->task->id]);
    }

    public function test_user_cannot_delete_task_in_other_workspace()
    {
        $otherWorkspace = Workspace::factory()->create();
        $otherTask = Task::factory()->create(['workspace_id' => $otherWorkspace->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/tasks/' . $otherTask->id);

        $response->assertStatus(403);

        // Ensure the task is not deleted from the database
        $this->assertDatabaseHas('tasks', ['id' => $otherTask->id]);
    }
}
