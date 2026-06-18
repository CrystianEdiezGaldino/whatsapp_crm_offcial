<?php
/** Teste: design system SisZap no CSS e layout. php tests/design-system-css-test.php */
$root = dirname(__DIR__);
$css = file_get_contents($root . '/resources/css/components.css');
$layout = file_get_contents($root . '/resources/views/layouts/app.blade.php');
$login = file_get_contents($root . '/resources/views/auth/login.blade.php');
$conversations = file_get_contents($root . '/resources/views/conversations/index.blade.php');

$checks = [
    [$css, '#1DA85A', 'cor primária no CSS'],
    [$css, '.btn-nm-primary', 'botão primário'],
    [$css, '.card-nm', 'card'],
    [$css, '.design-scrollbar', 'scrollbar do protótipo'],
    [$css, '@extend', 'remover @extend inválido'],
    [$layout, 'siszap-sidebar', 'sidebar clara'],
    [$layout, 'Figtree', 'fonte Figtree'],
    [$layout, 'fullHeight', 'modo fullHeight'],
    [$login, 'btn-auth', 'login com botão auth'],
    [$login, 'input-inset-wrap', 'login com inputs inset'],
    [file_get_contents($root . '/resources/views/dashboard.blade.php'), 'x-layout.page-header', 'dashboard usa page-header global'],
    [file_get_contents($root . '/resources/views/dashboard.blade.php'), 'form-grid--filters', 'dashboard usa form-grid global'],
    [$css, '.page-header__main', 'CSS page-header estruturado'],
    [$css, '.input-inset-wrap', 'classe input inset no CSS'],
    [$conversations, 'fullHeight', 'atendimentos usa layout global'],
    [$conversations, 'x-chat.list-item', 'lista de chats com componente global'],
    [$css, '.chat-list-scroll', 'margem lateral na lista de chats'],
    [$conversations, 'x-chat.queue-tabs', 'tabs da fila'],
    [$conversations, 'x-chat.contact-panel', 'painel de contato global'],
    [$css, '.contact-panel', 'CSS painel contato'],
    [file_get_contents($root . '/resources/views/contacts/index.blade.php'), 'x-common.search-input', 'contatos usa search global'],
    [file_get_contents($root . '/resources/views/contacts/index.blade.php'), 'x-common.contact-avatar', 'contatos usa avatar global'],
    [file_get_contents($root . '/resources/views/contacts/index.blade.php'), 'x-layout.data-table', 'contatos usa tabela global'],
];

foreach ($checks as [$content, $needle, $label]) {
    $found = str_contains($content, $needle);
    if ($needle === '@extend' ? $found : !$found) {
        fwrite(STDERR, "FALHA: {$label}\n");
        exit(1);
    }
}

echo "OK: design system SisZap aplicado nos arquivos base\n";
