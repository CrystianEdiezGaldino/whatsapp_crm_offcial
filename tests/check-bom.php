<?php
$files = [
    'app/Http/Controllers/ReportController.php',
    'app/Models/ConversationClaim.php',
];
foreach ($files as $f) {
    $b = substr(file_get_contents(__DIR__ . '/../' . $f), 0, 8);
    echo $f . ': ' . bin2hex($b) . PHP_EOL;
}
