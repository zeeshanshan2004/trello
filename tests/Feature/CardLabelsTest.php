<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Card;
use App\Models\Label;
use App\Models\ListModel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardLabelsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and authenticate
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Create workspace and attach user as owner
        $this->workspace = Workspace::factory()->create();
        $this->workspace->addMember($this->user->id, 'owner');
        
        // Create board, list, and card
        $this->board = Board::factory()->create(['workspace_id' => $this->workspace->id]);
        $this->board->sharedUsers()->attach($this->user->id);
        
        $this->list = ListModel::factory()->create(['board_id' => $this->board->id]);
        $this->card = Card::factory()->create(['list_id' => $this->list->id]);
        
        // Create labels for the board
        $this->label1 = Label::factory()->create(['board_id' => $this->board->id, 'name' => 'Bug', 'color' => '#ff0000']);
        $this->label2 = Label::factory()->create(['board_id' => $this->board->id, 'name' => 'Feature', 'color' => '#00ff00']);
        $this->label3 = Label::factory()->create(['board_id' => $this->board->id, 'name' => 'Enhancement', 'color' => '#0000ff']);
    }

    public function test_can_update_card_labels_with_valid_label_ids()
    {
        $this->withoutMiddleware();
        
        // Debug: Reload board from database to simulate route model binding
        $freshBoard = Board::find($this->board->id);
        dump([
            'fresh_board_id' => $freshBoard->id,
            'fresh_board_labels_count' => $freshBoard->labels()->count(),
            'fresh_board_labels_ids' => $freshBoard->labels->pluck('id')->toArray(),
        ]);
        
        $response = $this->postJson(
            route('cards.labels.update', [
                'board' => $this->board->id,
                'list' => $this->list->id,
                'card' => $this->card->id
            ]),
            ['labels' => [$this->label1->id, $this->label2->id]]
        );

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonCount(2, 'labels');

        // Verify database was updated
        $this->card->refresh();
        $this->assertEquals([$this->label1->id, $this->label2->id], $this->card->labels);
    }

    public function test_can_update_card_labels_with_empty_array()
    {
        $this->withoutMiddleware();
        
        // First add some labels
        $this->card->update(['labels' => [$this->label1->id]]);

        // Then remove all labels
        $response = $this->postJson(
            route('cards.labels.update', [
                'board' => $this->board->id,
                'list' => $this->list->id,
                'card' => $this->card->id
            ]),
            ['labels' => []]
        );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'labels' => []
            ]);

        // Verify database was updated
        $this->card->refresh();
        $this->assertEquals([], $this->card->labels);
    }

    public function test_rejects_invalid_label_id_not_belonging_to_board()
    {
        $this->withoutMiddleware();
        
        // Create another board with a label
        $otherBoard = Board::factory()->create(['workspace_id' => $this->workspace->id]);
        $otherLabel = Label::factory()->create(['board_id' => $otherBoard->id]);

        $response = $this->postJson(
            route('cards.labels.update', [
                'board' => $this->board->id,
                'list' => $this->list->id,
                'card' => $this->card->id
            ]),
            ['labels' => [$this->label1->id, $otherLabel->id]]
        );

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ])
            ->assertJsonFragment(['error']);

        // Verify database was not updated
        $this->card->refresh();
        $this->assertNotEquals([$this->label1->id, $otherLabel->id], $this->card->labels);
    }

    public function test_validates_labels_must_be_array()
    {
        $this->withoutMiddleware();
        
        $response = $this->postJson(
            route('cards.labels.update', [
                'board' => $this->board->id,
                'list' => $this->list->id,
                'card' => $this->card->id
            ]),
            ['labels' => 'not-an-array']
        );

        $response->assertStatus(422);
    }

    public function test_validates_label_ids_must_be_integers()
    {
        $this->withoutMiddleware();
        
        $response = $this->postJson(
            route('cards.labels.update', [
                'board' => $this->board->id,
                'list' => $this->list->id,
                'card' => $this->card->id
            ]),
            ['labels' => ['string-id', 'another-string']]
        );

        $response->assertStatus(422);
    }

    public function test_returns_full_label_data_in_response()
    {
        $this->withoutMiddleware();
        
        $response = $this->postJson(
            route('cards.labels.update', [
                'board' => $this->board->id,
                'list' => $this->list->id,
                'card' => $this->card->id
            ]),
            ['labels' => [$this->label1->id, $this->label3->id]]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'labels' => [
                    '*' => ['id', 'board_id', 'name', 'color']
                ]
            ]);

        $labels = $response->json('labels');
        $this->assertCount(2, $labels);
        $this->assertEquals($this->label1->name, $labels[0]['name']);
        $this->assertEquals($this->label3->name, $labels[1]['name']);
    }
}
