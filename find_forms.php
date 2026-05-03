<?php
$content = file_get_contents('resources/views/admin/dashboard.blade.php');
$lines = explode("\n", $content);
foreach($lines as $i => $line) {
    if (strpos($line, '<form') !== false) {
        echo 'FORM OPEN at '.($i+1).': '.trim($line)."\n";
    }
    if (strpos($line, '</form>') !== false) {
        echo 'FORM CLOSE at '.($i+1).': '.trim($line)."\n";
    }
}
