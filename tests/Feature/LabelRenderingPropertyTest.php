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

/**
 * Property-Based Test for Label Rendering Correctness
 * 
 * **Validates: Requirements 1.1, 1.3, 1.4, 1.5**
 * 
 * Property 1: Label Rendering Correctness
 * For any card with assigned labels, rendering the card should produce HTML containing 
 * a colored chip element for each label, where each chip displays the label's name as 
 * text content, uses the label's color as background, uses appropriate contrast color 
 * for text, and properly escapes the label name to prevent XSS.
 */
class LabelRenderingPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Workspace $workspace;
    protected Board $board;
    protected ListModel $list;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and authenticate
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Create workspace and attach user as owner
        $this->workspace = Workspace::factory()->create();
        $this->workspace->addMember($this->user->id, 'owner');
        
        // Create board and list
        $this->board = Board::factory()->create(['workspace_id' => $this->workspace->id]);
        $this->board->sharedUsers()->attach($this->user->id);
        
        $this->list = ListModel::factory()->create(['board_id' => $this->board->id]);
    }

    /**
     * Property Test: Label Rendering Correctness
     * 
     * Tests that for any card with assigned labels, the rendered HTML:
     * 1. Contains a colored chip element for each label
     * 2. Each chip displays the label's name as text content
     * 3. Each chip uses the label's color as background
     * 4. Each chip uses appropriate contrast color for text
     * 5. Label names are properly escaped to prevent XSS
     */
    public function test_label_rendering_correctness_property(): void
    {
        // Generate multiple test cases with different label configurations
        $testCases = $this->generateLabelTestCases();
        
        foreach ($testCases as $testCase) {
            $this->assertLabelRenderingProperty($testCase);
        }
    }

    /**
     * Generate diverse test cases for property-based testing
     * 
     * @return array Array of test cases with different label configurations
     */
    private function generateLabelTestCases(): array
    {
        $faker = \Faker\Factory::create();
        $testCases = [];
        
        // Test Case 1: Single label with safe name
        $testCases[] = [
            'labels' => [
                ['name' => 'Bug', 'color' => '#ff0000']
            ],
            'description' => 'Single label with safe name'
        ];
        
        // Test Case 2: Multiple labels with various colors
        $testCases[] = [
            'labels' => [
                ['name' => 'Feature', 'color' => '#00ff00'],
                ['name' => 'Enhancement', 'color' => '#0000ff'],
                ['name' => 'Documentation', 'color' => '#ffff00']
            ],
            'description' => 'Multiple labels with various colors'
        ];
        
        // Test Case 3: Labels with XSS attempt in name
        $testCases[] = [
            'labels' => [
                ['name' => '<script>alert("XSS")</script>', 'color' => '#ff00ff'],
                ['name' => '<img src=x onerror=alert(1)>', 'color' => '#00ffff']
            ],
            'description' => 'Labels with XSS attempts'
        ];
        
        // Test Case 4: Labels with special characters
        $testCases[] = [
            'labels' => [
                ['name' => 'Label & Special', 'color' => '#ff8800'],
                ['name' => 'Quote"Test', 'color' => '#8800ff'],
                ['name' => "Apostrophe's Test", 'color' => '#00ff88']
            ],
            'description' => 'Labels with special characters'
        ];
        
        // Test Case 5: Labels with very light colors (should use dark text)
        $testCases[] = [
            'labels' => [
                ['name' => 'Light Yellow', 'color' => '#ffff00'],
                ['name' => 'Light Cyan', 'color' => '#00ffff'],
                ['name' => 'White', 'color' => '#ffffff']
            ],
            'description' => 'Labels with light colors requiring dark text'
        ];
        
        // Test Case 6: Labels with very dark colors (should use light text)
        $testCases[] = [
            'labels' => [
                ['name' => 'Black', 'color' => '#000000'],
                ['name' => 'Dark Red', 'color' => '#800000'],
                ['name' => 'Dark Blue', 'color' => '#000080']
            ],
            'description' => 'Labels with dark colors requiring light text'
        ];
        
        // Test Case 7: Random labels with faker
        for ($i = 0; $i < 5; $i++) {
            $numLabels = $faker->numberBetween(1, 5);
            $labels = [];
            for ($j = 0; $j < $numLabels; $j++) {
                $labels[] = [
                    'name' => $faker->words($faker->numberBetween(1, 3), true),
                    'color' => $faker->hexColor()
                ];
            }
            $testCases[] = [
                'labels' => $labels,
                'description' => "Random test case $i with $numLabels labels"
            ];
        }
        
        // Test Case 8: Labels with long names
        $testCases[] = [
            'labels' => [
                ['name' => str_repeat('Long ', 20), 'color' => '#ff0000'],
                ['name' => $faker->sentence(20), 'color' => '#00ff00']
            ],
            'description' => 'Labels with very long names'
        ];
        
        // Test Case 9: Labels with unicode characters
        $testCases[] = [
            'labels' => [
                ['name' => '🐛 Bug', 'color' => '#ff0000'],
                ['name' => '✨ Feature', 'color' => '#00ff00'],
                ['name' => '中文标签', 'color' => '#0000ff']
            ],
            'description' => 'Labels with unicode and emoji characters'
        ];
        
        return $testCases;
    }

    /**
     * Assert that the label rendering property holds for a given test case
     * 
     * @param array $testCase Test case with labels and description
     */
    private function assertLabelRenderingProperty(array $testCase): void
    {
        // Create labels for the board
        $createdLabels = [];
        foreach ($testCase['labels'] as $labelData) {
            $createdLabels[] = Label::factory()->create([
                'board_id' => $this->board->id,
                'name' => $labelData['name'],
                'color' => $labelData['color']
            ]);
        }
        
        // Create card with assigned labels
        $card = Card::factory()->create([
            'list_id' => $this->list->id,
            'labels' => collect($createdLabels)->pluck('id')->toArray()
        ]);
        
        // Render the card view
        $response = $this->get(route('cards.show', [
            'board' => $this->board->id,
            'list' => $this->list->id,
            'card' => $card->id
        ]));
        
        $html = $response->getContent();
        
        // Assert: For each label, verify rendering correctness
        foreach ($createdLabels as $label) {
            $this->assertLabelRenderedCorrectly($html, $label, $testCase['description']);
        }
        
        // Clean up for next test case
        foreach ($createdLabels as $label) {
            $label->delete();
        }
        $card->delete();
    }

    /**
     * Assert that a specific label is rendered correctly in the HTML
     * 
     * @param string $html The rendered HTML content
     * @param Label $label The label to verify
     * @param string $testDescription Description of the test case
     */
    private function assertLabelRenderedCorrectly(string $html, Label $label, string $testDescription): void
    {
        // Property 1.1: HTML contains a colored chip element for the label
        $this->assertStringContainsString(
            'card-label-item',
            $html,
            "[$testDescription] HTML should contain card-label-item class"
        );
        
        // Property 1.4: Chip displays the label's name as text content (escaped)
        $escapedName = htmlspecialchars($label->name, ENT_QUOTES, 'UTF-8');
        $this->assertStringContainsString(
            $escapedName,
            $html,
            "[$testDescription] HTML should contain escaped label name: {$label->name}"
        );
        
        // Property 1.5: Label names are properly escaped (XSS prevention)
        // If the label name contains script tags, they should be escaped
        if (str_contains($label->name, '<script>') || str_contains($label->name, '<img')) {
            $this->assertStringNotContainsString(
                '<script>',
                $html,
                "[$testDescription] HTML should not contain unescaped script tags"
            );
            $this->assertStringNotContainsString(
                '<img src=x onerror=',
                $html,
                "[$testDescription] HTML should not contain unescaped img tags with onerror"
            );
        }
        
        // Property 1.3: Chip uses the label's color as background
        $this->assertStringContainsString(
            $label->color,
            $html,
            "[$testDescription] HTML should contain label color: {$label->color}"
        );
        
        // Property 1.3: Chip uses appropriate contrast color for text
        $expectedTextColor = $this->getExpectedContrastColor($label->color);
        $this->assertStringContainsString(
            $expectedTextColor,
            $html,
            "[$testDescription] HTML should contain appropriate contrast text color: {$expectedTextColor} for background: {$label->color}"
        );
    }

    /**
     * Calculate the expected contrast color for a given background color
     * This mirrors the logic in the getContrastColor helper function
     * 
     * @param string $hexColor Background color in hex format
     * @return string Expected text color ('#172b4d' or '#ffffff')
     */
    private function getExpectedContrastColor(string $hexColor): string
    {
        // Remove # if present
        $hex = ltrim($hexColor, '#');
        
        // Convert hex to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Calculate brightness using the same formula as the helper
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        
        // Return dark text for light backgrounds, light text for dark backgrounds
        return $brightness > 155 ? '#172b4d' : '#ffffff';
    }

    /**
     * Test edge case: Card with no labels should not display label elements
     * 
     * **Validates: Requirement 1.2**
     */
    public function test_card_with_no_labels_displays_no_label_elements(): void
    {
        // Create card with no labels
        $card = Card::factory()->create([
            'list_id' => $this->list->id,
            'labels' => []
        ]);
        
        // Render the card view
        $response = $this->get(route('cards.show', [
            'board' => $this->board->id,
            'list' => $this->list->id,
            'card' => $card->id
        ]));
        
        $html = $response->getContent();
        
        // The card-labels-display container may exist but should be empty or hidden
        // We verify that no card-label-item elements are present
        $labelItemCount = substr_count($html, 'card-label-item');
        
        $this->assertEquals(
            0,
            $labelItemCount,
            'Card with no labels should not display any card-label-item elements'
        );
    }

    /**
     * Test edge case: Card with deleted label IDs should filter them out
     * 
     * **Validates: Requirement 7.4**
     */
    public function test_card_with_deleted_label_ids_filters_them_out(): void
    {
        // Create labels
        $label1 = Label::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Valid Label',
            'color' => '#00ff00'
        ]);
        
        $label2 = Label::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'To Be Deleted',
            'color' => '#ff0000'
        ]);
        
        // Create card with both labels
        $card = Card::factory()->create([
            'list_id' => $this->list->id,
            'labels' => [$label1->id, $label2->id]
        ]);
        
        // Delete one label
        $deletedLabelId = $label2->id;
        $label2->delete();
        
        // Render the card view
        $response = $this->get(route('cards.show', [
            'board' => $this->board->id,
            'list' => $this->list->id,
            'card' => $card->id
        ]));
        
        $html = $response->getContent();
        
        // Assert: Valid label should be displayed
        $this->assertStringContainsString(
            htmlspecialchars($label1->name, ENT_QUOTES, 'UTF-8'),
            $html,
            'Valid label should be displayed'
        );
        
        // Assert: Deleted label should not be displayed
        $this->assertStringNotContainsString(
            'To Be Deleted',
            $html,
            'Deleted label should not be displayed'
        );
    }
}
