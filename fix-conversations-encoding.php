<?php
/**
 * Corrige textos corrompidos (??) no blade de conversas.
 * Uso: php fix-conversations-encoding.php
 */
$file = __DIR__ . '/resources/views/conversations/index.blade.php';
$content = file_get_contents($file);

$map = [
    'Notifica????o' => 'Notificação',
    'Notifica????es' => 'Notificações',
    'notifica????o' => 'notificação',
    'notifica????es' => 'notificações',
    'Voc??' => 'Você',
    'voc??' => 'você',
    'a????o' => 'ação',
    'A????o' => 'Ação',
    'Hist??rico' => 'Histórico',
    'hist??rico' => 'histórico',
    'Indispon??vel' => 'Indisponível',
    'relat??rios' => 'relatórios',
    'Necess??rio' => 'Necessário',
    'Coment??rios' => 'Comentários',
    'reaber????' => 'reaberta',
    'ser??' => 'será',
    'n??o' => 'não',
    'N??o' => 'Não',
    'inv??lida' => 'inválida',
    'dispon??vel' => 'disponível',
    'Fun????es' => 'Funções',
    'reatribui????o' => 'reatribuição',
    'R??pido' => 'Rápido',
    'espa??o' => 'espaço',
    ' ?? comando' => ' é comando',
    'n??o encontrado' => 'não encontrado',
    'permiss??o' => 'permissão',
    'Dura????o' => 'Duração',
    'Satisfeito' => 'Satisfeito',
    '??????' => '✅',
    '????' => '📩',
    '???' => '✓',
    '???????' => '🏷️',
    '???? Pedir' => '🔓 Pedir',
    '???? Hist??rico' => '📋 Histórico',
    '?????? Encerrar' => '✔️ Encerrar',
    '??? R??pido' => '⚡ Rápido',
    '\\??\\s' => '\\às',
    '???? ' => '📅 ',
    '?????? ' => '⏱️ ',
    '???? ' => '💬 ',
    '???? Atendido' => '👤 Atendido',
    '???? ' => '📞 ',
    '??? Resolvido' => '✓ Resolvido',
    '???? {{' => '👤 {{',
    '???? Problema' => '✅ Problema',
    '???? Cliente' => '😊 Cliente',
    '??? Acompanhamento' => '📋 Acompanhamento',
    '???? Conversa' => '📋 Conversa',
    '?????? Spam' => '🚫 Spam',
    '?????? Sem Resposta' => '⏳ Sem Resposta',
    '?????? Transferido' => '↪️ Transferido',
    '??? Outro' => '📝 Outro',
    'h?? ' => 'há ',
];

foreach ($map as $from => $to) {
    $content = str_replace($from, $to, $content);
}

file_put_contents($file, $content);
echo "OK: encoding corrigido em $file\n";
