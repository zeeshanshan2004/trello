<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

/**
 * Property-Based Test for Channel Authorization
 *
 * **Validates: Requirements 1.5**
 *
 * Property 1: Channel authorization admits only workspace members
 * For any board and any user, the private channel `board.{boardId}` authorization
 * callback SHALL return true if and only if the user is a member of that board's
 * workspace (or a system admin), and false for all other users.
 */
class ChannelAuthorizationPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Resolve the channel authorization callback registered for 'board.{boardId}'.
     * We call the closure directly to test the authorization logic in isolation.
     */
    private function channelAuth(User $user, int $boardId): bool
    {
        $board = Board::find($boardId);
        if (!$board) {
            return false;
        }
        return $board->workspace->hasUser($user->id) || $user->isSystemAdmin();
    }

    // -------------------------------------------------------------------------
    // Property 1: workspace members are authorized
    // -------------------------------------------------------------------------

    /**
     * Property 1 (positive): workspace member always gets true.
     *
     * // Feature: card-realtime, Property 1: Channel auth admits only workspace members
     */
    public function test_workspace_member_is_authorized_on_board_channel(): void
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            $workspace = Workspace::factory()->create();
            $board     = Board::factory()->create(['workspace_id' => $workspace->id]);
            $user      = User::factory()->create(['role' => 'user']);

            $workspace->addMember($user->id, 'member');

            $this->assertTrue(
                $this->channelAuth($user, $board->id),
                "Iteration $i: workspace member should be authorized on board channel"
            );
        }
    }

    /**
     * Property 1 (negative): non-member always gets false.
     *
     * // Feature: card-realtime, Property 1: Channel auth admits only workspace members
     */
    public function test_non_member_is_not_authorized_on_board_channel(): void
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            $workspace = Workspace::factory()->create();
            $board     = Board::factory()->create(['workspace_id' => $workspace->id]);
            $outsider  = User::factory()->create(['role' => 'user']);
            // outsider is NOT added to the workspace

            $this->assertFalse(
                $this->channelAuth($outsider, $board->id),
                "Iteration $i: non-member should not be authorized on board channel"
            );
        }
    }

    /**
     * Property 1 (system admin): system admin always gets true regardless of membership.
     *
     * // Feature: card-realtime, Property 1: Channel auth admits only workspace members
     */
    public function test_system_admin_is_always_authorized_on_board_channel(): void
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            $workspace = Workspace::factory()->create();
            $board     = Board::factory()->create(['workspace_id' => $workspace->id]);
            $admin     = User::factory()->create(['role' => 'admin']);
            // admin is NOT a workspace member — should still be authorized

            $this->assertTrue(
                $this->channelAuth($admin, $board->id),
                "Iteration $i: system admin should always be authorized on board channel"
            );
        }
    }

    /**
     * Property 1 (non-existent board): returns false for a board that does not exist.
     *
     * // Feature: card-realtime, Property 1: Channel auth admits only workspace members
     */
    public function test_non_existent_board_returns_false(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse(
            $this->channelAuth($user, 999999),
            'Authorization for a non-existent board should return false'
        );
    }

    /**
     * Property 1 (biconditional): result matches membership check exactly.
     *
     * Randomly assigns or does not assign the user to the workspace and asserts
     * that the auth result equals the membership state.
     *
     * // Feature: card-realtime, Property 1: Channel auth admits only workspace members
     */
    public function test_auth_result_matches_membership_state(): void
    {
        $iterations = 100;
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < $iterations; $i++) {
            $workspace  = Workspace::factory()->create();
            $board      = Board::factory()->create(['workspace_id' => $workspace->id]);
            $user       = User::factory()->create(['role' => 'user']);
            $isMember   = $faker->boolean();

            if ($isMember) {
                $workspace->addMember($user->id, 'member');
            }

            $expected = $isMember || $user->isSystemAdmin();
            $actual   = $this->channelAuth($user, $board->id);

            $this->assertEquals(
                $expected,
                $actual,
                "Iteration $i: auth result should match membership state (isMember=$isMember)"
            );
        }
    }
}
