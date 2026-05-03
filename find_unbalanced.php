<?php
$lines = file('test_compiled.php');
$stack = [];
foreach ($lines as $index => $line) {
    if (preg_match('/<\?php\s+if\s*\(/', $line)) {
        $stack[] = ['type' => 'if', 'line' => $index + 1];
    } elseif (preg_match('/<\?php\s+elseif\s*\(/', $line)) {
        // Just part of if block
    } elseif (preg_match('/<\?php\s+else\s*:/', $line)) {
        // Just part of if block
    } elseif (preg_match('/<\?php\s+endif;/', $line)) {
        if (empty($stack)) {
            echo "Unexpected endif at line " . ($index + 1) . "\n";
        } else {
            array_pop($stack);
        }
    }
}
if (!empty($stack)) {
    echo "Unclosed if statements:\n";
    print_r($stack);
} else {
    echo "All balanced.\n";
}
