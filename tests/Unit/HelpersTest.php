<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    /**
     * Test getContrastColor returns dark text for light backgrounds.
     */
    public function test_returns_dark_text_for_light_backgrounds(): void
    {
        // White background
        $this->assertEquals('#172b4d', getContrastColor('#ffffff'));
        $this->assertEquals('#172b4d', getContrastColor('ffffff'));
        
        // Yellow background (bright)
        $this->assertEquals('#172b4d', getContrastColor('#ffff00'));
        
        // Light gray
        $this->assertEquals('#172b4d', getContrastColor('#cccccc'));
    }

    /**
     * Test getContrastColor returns light text for dark backgrounds.
     */
    public function test_returns_light_text_for_dark_backgrounds(): void
    {
        // Black background
        $this->assertEquals('#ffffff', getContrastColor('#000000'));
        $this->assertEquals('#ffffff', getContrastColor('000000'));
        
        // Red background
        $this->assertEquals('#ffffff', getContrastColor('#ff0000'));
        
        // Blue background
        $this->assertEquals('#ffffff', getContrastColor('#0000ff'));
        
        // Green background
        $this->assertEquals('#ffffff', getContrastColor('#00ff00'));
        
        // Dark gray
        $this->assertEquals('#ffffff', getContrastColor('#333333'));
    }

    /**
     * Test getContrastColor handles hex colors with and without # prefix.
     */
    public function test_handles_hex_colors_with_and_without_hash(): void
    {
        $this->assertEquals(
            getContrastColor('#ffffff'),
            getContrastColor('ffffff')
        );
        
        $this->assertEquals(
            getContrastColor('#000000'),
            getContrastColor('000000')
        );
    }

    /**
     * Test getContrastColor with various common label colors.
     */
    public function test_common_label_colors(): void
    {
        // Trello-like label colors
        $this->assertEquals('#ffffff', getContrastColor('#61bd4f')); // Green - dark, needs light text
        $this->assertEquals('#172b4d', getContrastColor('#f2d600')); // Yellow - bright, needs dark text
        $this->assertEquals('#172b4d', getContrastColor('#ff9f1a')); // Orange - bright, needs dark text
        $this->assertEquals('#ffffff', getContrastColor('#eb5a46')); // Red - dark, needs light text
        $this->assertEquals('#ffffff', getContrastColor('#c377e0')); // Purple - dark, needs light text
        $this->assertEquals('#ffffff', getContrastColor('#0079bf')); // Blue - dark, needs light text
    }
}
