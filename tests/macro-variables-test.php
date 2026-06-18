<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Contact;
use App\Models\Conversation;
use App\Services\VariableResolver;

$contact = new Contact(['name' => 'Dennis Coleti', 'phone' => '554196034978']);
$conv = new Conversation();
$conv->setRelation('contact', $contact);
$conv->setRelation('sector', null);

$text = 'Olá, {nome}! Bem-vindo. Tel: {{telefone}}';
$out = app(VariableResolver::class)->replaceInText($text, $conv);

$ok = str_contains($out, 'Dennis Coleti') && !str_contains($out, '{nome}');
echo ($ok ? 'PASS' : 'FAIL') . ": $out\n";
exit($ok ? 0 : 1);
