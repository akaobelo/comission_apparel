<?php
$file = 'resources/views/admin/dashboard.blade.php';
$lines = file($file);

$replaced = 0;
foreach ($lines as $i => $line) {
    // Check if line contains "@foreach($designCollections as $collection)" and it's inside the collectionItems catalog block (around line 1330)
    // Actually we can check if it matches that exact string (ignoring whitespace differences)
    if (strpos($line, '@foreach($designCollections as $collection)') !== false) {
        // We only want to replace it when it's nested (e.g. line 1330 and 1939)
        // Let's replace both to be safe!
        $lines[$i] = str_replace('$collection', '$col', $line);
        
        // Also look at the next line (which contains the <option ...>)
        $nextLine = $lines[$i+1];
        $lines[$i+1] = str_replace('$collection', '$col', $nextLine);
        
        // And the line after that (which is @endforeach)
        // Wait, @endforeach doesn't have variables, but we can verify
        
        echo "Replaced at line " . ($i + 1) . "\n";
        $replaced++;
    }
}

file_put_contents($file, implode("", $lines));
echo "Completed: Replaced {$replaced} occurrences.\n";
