<?php
$tokens = token_get_all(file_get_contents('test_compiled.php'));
$stack = [];
$lineMap = [];

foreach ($tokens as $token) {
    if (is_array($token)) {
        $name = token_name($token[0]);
        $text = strtolower($token[1]);
        $line = $token[2];

        if ($name === 'T_IF' || $name === 'T_FOREACH' || $name === 'T_WHILE' || $name === 'T_FOR') {
            // we only care about alternative syntax
        }
        
        // Let's just do a simple regex on the file instead since token_get_all is complex
    }
}
