<?php
$lines = file('storage/logs/laravel.log');
$lastLines = array_slice($lines, -100);
echo implode("", $lastLines);
