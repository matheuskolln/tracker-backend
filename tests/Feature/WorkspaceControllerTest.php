<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and generate a token
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('TestToken')->plainTextToken;
    }

    // Test listing workspaces
    public function test_user_can_list_workspaces()
    {
        // Create workspaces associated with the user
        $workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
        $workspace->users()->attach($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/workspaces');

        $response->assertStatus(200)
                 ->assertJsonStructure([['id', 'name', 'owner_id']]);
    }

    // Test creating a workspace
    public function test_user_can_create_workspace()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/workspaces', [
            'name' => 'New Workspace',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['name' => 'New Workspace', 'owner_id' => $this->user->id]);
    }

    // Test viewing a workspace
    public function test_user_can_view_workspace()
    {
        $workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
        $workspace->users()->attach($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/workspaces/' . $workspace->id);

        $response->assertStatus(200)
                 ->assertJson(['id' => $workspace->id, 'name' => $workspace->name]);
    }

    // Test user cannot view a workspace they don't belong to
    public function test_user_cannot_view_unauthorized_workspace()
    {
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->create(['owner_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/workspaces/' . $workspace->id);

        $response->assertStatus(403)
                 ->assertJson(['error' => 'Você não tem permissão para acessar este workspace.']);
    }

    // Test adding a user to a workspace
    public function test_owner_can_add_user_to_workspace()
    {
        $workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
        $newUser = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/workspaces/' . $workspace->id . '/add-user', [
            'user_id' => $newUser->id,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Usuário adicionado ao workspace.']);

        // Check if the user was added to the workspace
        $this->assertTrue($workspace->users->contains($newUser));
    }

    // Test non-owner cannot add a user to a workspace
    public function test_non_owner_cannot_add_user_to_workspace()
    {
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->create(['owner_id' => $otherUser->id]);
        $newUser = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/workspaces/' . $workspace->id . '/add-user', [
            'user_id' => $newUser->id,
        ]);

        $response->assertStatus(403)
                 ->assertJson(['error' => 'Você não é o dono deste workspace.']);
    }

    // Test adding a user who is already in the workspace
    public function test_cannot_add_user_already_in_workspace()
    {
        $workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
        $existingUser = User::factory()->create();
        $workspace->users()->attach($existingUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/workspaces/' . $workspace->id . '/add-user', [
            'user_id' => $existingUser->id,
        ]);

        $response->assertStatus(400)
                 ->assertJson(['error' => 'Usuário já está no workspace.']);
    }

    // Test removing a user from a workspace
    public function test_owner_can_remove_user_from_workspace()
    {
        $workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
        $userToRemove = User::factory()->create();
        $workspace->users()->attach($userToRemove);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/workspaces/' . $workspace->id . '/remove-user', [
            'user_id' => $userToRemove->id,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Usuário removido do workspace.']);

        // Check if the user was removed from the workspace
        $this->assertFalse($workspace->users->contains($userToRemove));
    }

    // Test non-owner cannot remove a user from a workspace
    public function test_non_owner_cannot_remove_user_from_workspace()
    {
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->create(['owner_id' => $otherUser->id]);
        $userToRemove = User::factory()->create();
        $workspace->users()->attach($userToRemove);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/workspaces/' . $workspace->id . '/remove-user', [
            'user_id' => $userToRemove->id,
        ]);

        $response->assertStatus(403)
                 ->assertJson(['error' => 'Você não é o dono deste workspace.']);
    }

    // Test owner cannot remove themselves from the workspace
    public function test_owner_cannot_remove_themselves_from_workspace()
    {
        $workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/workspaces/' . $workspace->id . '/remove-user', [
            'user_id' => $this->user->id,
        ]);

        $response->assertStatus(400)
                 ->assertJson(['error' => 'Você não pode se remover do workspace.']);
    }
}
