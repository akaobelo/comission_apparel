<?php
$content = file_get_contents('resources/views/admin/dashboard.blade.php');

// Simple regex parser for <form> and </form>
preg_match_all('/<\/?form\b[^>]*>/i', $content, $matches, PREG_OFFSET_CAPTURE);

$openForms = [];
foreach ($matches[0] as $match) {
    $tag = $match[0];
    $pos = $match[1];
    
    // Find line number
    $line = substr_count(substr($content, 0, $pos), "\n") + 1;
    
    if (strpos($tag, '</') === 0) {
        if (empty($openForms)) {
            echo "Unexpected </form> at line $line\n";
        } else {
            array_pop($openForms);
        }
    } else {
        $openForms[] = ['tag' => $tag, 'line' => $line];
    }
}

if (!empty($openForms)) {
    echo "Unclosed forms:\n";
    foreach ($openForms as $form) {
        echo "  - Open form at line {$form['line']}: " . htmlspecialchars($form['tag']) . "\n";
    }
} else {
    echo "All form tags are balanced!\n";
}
