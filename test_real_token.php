<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get token from .env
$token = env('WHATSAPP_ACCESS_TOKEN');

if (!$token) {
    echo "⚠️  No WHATSAPP_ACCESS_TOKEN in .env\n";
    exit(1);
}

echo "🔑 Token length: " . strlen($token) . " chars\n";

// Create request
$request = new \Illuminate\Http\Request([
    'access_token' => $token
]);

$controller = new \App\Http\Controllers\Admin\WhatsAppNumberController();

echo "Testing syncFromMeta with real token:\n";
$response = $controller->syncFromMeta($request);
echo "Response Status: " . $response->status() . "\n";

$data = json_decode($response->getContent(), true);
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

if ($data['success']) {
    echo "\n✅ Import successful!\n";
    echo "Numbers imported: " . ($data['imported_count'] ?? 0) . "\n";
    
    // Check database
    $count = \App\Models\WhatsAppNumber::count();
    echo "Total numbers in DB: $count\n";
} else {
    echo "\n⚠️  Import failed\n";
}
