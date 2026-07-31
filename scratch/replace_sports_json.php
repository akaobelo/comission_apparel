<?php
$file = 'resources/views/admin/dashboard.blade.php';
$lines = file($file);

$replaced = 0;
foreach ($lines as $i => $line) {
    if (strpos($line, 'options: {{ json_encode(is_array($availableSports)') !== false) {
        // Replace this line content
        $lines[$i] = preg_replace('/options:\s*\{\{\s*json_encode\(.*?\)\s*\}\}/', 'options: window.availableSportsList', $line);
        echo "Replaced options at line " . ($i + 1) . "\n";
        $replaced++;
    }
}

file_put_contents($file, implode("", $lines));
echo "Completed: Replaced {$replaced} occurrences.\n";
