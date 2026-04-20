<?php
$file = 'resources/views/boards/show.blade.php';
$lines = file($file);

foreach ($lines as $i => $line) {
    if (strpos($line, '</div>div>') !== false) {
        $lines[$i] = str_replace('</div>div>', '</div>', $line);
        // Remove the next line if it's just </div>
        if (isset($lines[$i+1]) && trim($lines[$i+1]) === '</div>') {
            unset($lines[$i+1]);
        }
        echo "Fixed at line " . ($i+1) . "\n";
        break;
    }
}

file_put_contents($file, implode('', $lines));
echo "Done!\n";
