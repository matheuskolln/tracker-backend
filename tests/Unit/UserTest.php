<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_have_many_tasks()
    {
        // Create a user and some tasks
        $user = User::factory()->create();
        $task1 = Task::factory()->create();
        $task2 = Task::factory()->create();

        // Attach tasks to the user
        $user->tasks()->attach([$task1->id, $task2->id]);

        // Assert the user has the correct tasks
        $this->assertCount(2, $user->tasks);
    }

    /** @test */
    public function a_user_can_have_many_workspaces()
    {
        // Create a user and some workspaces
        $user = User::factory()->create();
        $workspace1 = Workspace::factory()->create();
        $workspace2 = Workspace::factory()->create();

        // Attach workspaces to the user
        $user->workspaces()->attach([$workspace1->id, $workspace2->id]);

        // Assert the user has the correct workspaces
        $this->assertCount(2, $user->workspaces);
    }

    /** @test */
    public function a_user_can_own_workspaces()
    {
        // Create a user and some workspaces
        $user = User::factory()->create();
        $workspace1 = Workspace::factory()->create(['owner_id' => $user->id]);
        $workspace2 = Workspace::factory()->create(['owner_id' => $user->id]);

        // Assert the user owns the correct workspaces
        $this->assertCount(2, $user->ownedWorkspaces);
    }

    /** @test */
    public function user_casts_password_to_hashed()
    {
        // Create a user with a password
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        // Assert the password is cast to hashed
        $this->assertTrue(password_verify('password123', $user->password));
    }

    /** @test */
    public function user_casts_email_verified_at_to_datetime()
    {
        // Create a user with an email_verified_at timestamp
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Assert the email_verified_at is cast to a datetime
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }
}
