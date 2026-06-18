<?php
/** Teste: emoji-picker.js existe e usa Unicode escapes. */
$path = __DIR__ . '/../public/js/helpers/emoji-picker.js';
$content = file_get_contents($path);
$ok = str_contains($content, 'initEmojiPicker')
    && str_contains($content, '\u{1F44D}')
    && str_contains($content, 'emojiPicker');
echo ($ok ? 'PASS' : 'FAIL') . ": emoji-picker.js\n";
exit($ok ? 0 : 1);
