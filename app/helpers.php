<?php

if (!function_exists('getContrastColor')) {
    /**
     * Calculate the appropriate text color (dark or light) based on background color brightness.
     * 
     * Uses the relative luminance formula to determine if a background color is light or dark,
     * then returns an appropriate contrasting text color for optimal readability.
     * 
     * @param string $hexColor The background color in hex format (with or without #)
     * @return string The contrasting text color ('#172b4d' for light backgrounds, '#ffffff' for dark backgrounds)
     */
    function getContrastColor(string $hexColor): string
    {
        // Remove # if present
        $hexColor = ltrim($hexColor, '#');
        
        // Convert hex to RGB
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        
        // Calculate brightness using relative luminance formula
        // This formula weights the RGB values according to human perception
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        
        // Return dark text for light backgrounds, light text for dark backgrounds
        return $brightness > 155 ? '#172b4d' : '#ffffff';
    }
}
