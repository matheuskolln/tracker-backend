<?php

namespace Tests\Unit;

use App\Models\Workspace;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_workspace_belongs_to_an_owner()
    {
        // Create a user and a workspace
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['owner_id' => $user->id]);

        // Assert the workspace belongs to the correct user
        $this->assertEquals($user->id, $workspace->owner->id);
    }

    /** @test */
    public function a_workspace_can_have_many_users()
    {
        // Create a workspace and some users
        $workspace = Workspace::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Attach users to the workspace
        $workspace->users()->attach([$user1->id, $user2->id]);

        // Assert the workspace has the correct users
        $this->assertCount(2, $workspace->users);
    }

    /** @test */
    public function a_workspace_can_have_many_tasks()
    {
        // Create a workspace and some tasks
        $workspace = Workspace::factory()->create();
        $task1 = Task::factory()->create(['workspace_id' => $workspace->id]);
        $task2 = Task::factory()->create(['workspace_id' => $workspace->id]);

        // Assert the workspace has the correct tasks
        $this->assertCount(2, $workspace->tasks);
    }
}
