<?php
$lines = file('test_compiled.php');
$stack = [];
foreach ($lines as $index => $line) {
    if (preg_match('/<\?php\s+if\b/', $line)) {
        $stack[] = ['type' => 'if', 'line' => $index + 1];
        echo "+ IF at line " . ($index + 1) . "\n";
    } elseif (preg_match('/<\?php\s+endif;/', $line)) {
        if (empty($stack)) {
            echo "Unexpected endif at line " . ($index + 1) . "\n";
        } else {
            $popped = array_pop($stack);
            echo "- ENDIF at line " . ($index + 1) . " closing IF from " . $popped['line'] . "\n";
        }
    }
}
