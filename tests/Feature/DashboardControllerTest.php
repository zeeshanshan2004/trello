<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: create a regular (non-admin) user.
     */
    private function createMember(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['role' => 'user'], $attrs));
    }

    /**
     * Helper: create a system-admin user.
     */
    private function createAdmin(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['role' => 'admin'], $attrs));
    }

    /**
     * Test 1: member user ke ownedWorkspaces mein sirf owner/admin pivot role wale workspaces hain.
     * Validates: Requirements 1.3, 5.1
     */
    public function test_owned_workspaces_contains_only_owner_and_admin_pivot_roles(): void
    {
        $user = $this->createMember();

        $ownerWorkspace = Workspace::factory()->create(['name' => 'Owner WS']);
        $adminWorkspace = Workspace::factory()->create(['name' => 'Admin WS']);
        $memberWorkspace = Workspace::factory()->create(['name' => 'Member WS']);

        $user->workspaces()->attach($ownerWorkspace->id, ['role' => 'owner']);
        $user->workspaces()->attach($adminWorkspace->id, ['role' => 'admin']);
        $user->workspaces()->attach($memberWorkspace->id, ['role' => 'member']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        $ownedWorkspaces = $response->viewData('ownedWorkspaces');

        $this->assertCount(2, $ownedWorkspaces);
        $this->assertTrue($ownedWorkspaces->contains('id', $ownerWorkspace->id));
        $this->assertTrue($ownedWorkspaces->contains('id', $adminWorkspace->id));
        $this->assertFalse($ownedWorkspaces->contains('id', $memberWorkspace->id));
    }

    /**
     * Test 2: member user ke memberWorkspaces mein sirf member pivot role wale workspaces hain.
     * Validates: Requirements 2.3, 5.2
     */
    public function test_member_workspaces_contains_only_member_pivot_role(): void
    {
        $user = $this->createMember();

        $ownerWorkspace = Workspace::factory()->create(['name' => 'Owner WS']);
        $memberWorkspace1 = Workspace::factory()->create(['name' => 'Member WS 1']);
        $memberWorkspace2 = Workspace::factory()->create(['name' => 'Member WS 2']);

        $user->workspaces()->attach($ownerWorkspace->id, ['role' => 'owner']);
        $user->workspaces()->attach($memberWorkspace1->id, ['role' => 'member']);
        $user->workspaces()->attach($memberWorkspace2->id, ['role' => 'member']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        $memberWorkspaces = $response->viewData('memberWorkspaces');

        $this->assertCount(2, $memberWorkspaces);
        $this->assertTrue($memberWorkspaces->contains('id', $memberWorkspace1->id));
        $this->assertTrue($memberWorkspaces->contains('id', $memberWorkspace2->id));
        $this->assertFalse($memberWorkspaces->contains('id', $ownerWorkspace->id));
    }

    /**
     * Test 3: koi workspace nahi hone par dono collections empty hain.
     * Validates: Requirements 5.4
     */
    public function test_both_collections_empty_when_user_has_no_workspaces(): void
    {
        $user = $this->createMember();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        $ownedWorkspaces = $response->viewData('ownedWorkspaces');
        $memberWorkspaces = $response->viewData('memberWorkspaces');

        $this->assertCount(0, $ownedWorkspaces);
        $this->assertCount(0, $memberWorkspaces);
    }

    /**
     * Test 4: admin user ke memberWorkspaces mein owned workspaces nahi hain.
     * Validates: Requirements 3.3
     */
    public function test_admin_member_workspaces_does_not_contain_owned_workspaces(): void
    {
        $admin = $this->createAdmin();

        $ownedWorkspace = Workspace::factory()->create(['name' => 'Admin Owned WS']);
        $otherWorkspace = Workspace::factory()->create(['name' => 'Other WS']);

        $admin->workspaces()->attach($ownedWorkspace->id, ['role' => 'owner']);
        // otherWorkspace is not attached to admin — it belongs to someone else

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);

        $ownedWorkspaces = $response->viewData('ownedWorkspaces');
        $memberWorkspaces = $response->viewData('memberWorkspaces');

        // Admin's owned workspace should NOT appear in memberWorkspaces
        $this->assertFalse($memberWorkspaces->contains('id', $ownedWorkspace->id));

        // Admin's owned workspace should appear in ownedWorkspaces
        $this->assertTrue($ownedWorkspaces->contains('id', $ownedWorkspace->id));
    }

    /**
     * Test 5: ownedWorkspaces aur memberWorkspaces mutually exclusive hain (intersection empty).
     * Validates: Requirements 5.3
     */
    public function test_owned_and_member_workspaces_are_mutually_exclusive(): void
    {
        $user = $this->createMember();

        $ownerWorkspace = Workspace::factory()->create(['name' => 'Owner WS']);
        $adminWorkspace = Workspace::factory()->create(['name' => 'Admin WS']);
        $memberWorkspace = Workspace::factory()->create(['name' => 'Member WS']);

        $user->workspaces()->attach($ownerWorkspace->id, ['role' => 'owner']);
        $user->workspaces()->attach($adminWorkspace->id, ['role' => 'admin']);
        $user->workspaces()->attach($memberWorkspace->id, ['role' => 'member']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        $ownedWorkspaces = $response->viewData('ownedWorkspaces');
        $memberWorkspaces = $response->viewData('memberWorkspaces');

        $ownedIds = $ownedWorkspaces->pluck('id');
        $memberIds = $memberWorkspaces->pluck('id');

        // Intersection must be empty
        $intersection = $ownedIds->intersect($memberIds);
        $this->assertCount(0, $intersection, 'ownedWorkspaces aur memberWorkspaces mein koi workspace common nahi hona chahiye');
    }
}
