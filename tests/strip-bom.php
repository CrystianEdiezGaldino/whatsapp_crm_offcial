<?php
/** Remove UTF-8 BOM de arquivos PHP (uso único / manutenção). */
$root = dirname(__DIR__);
$files = array_slice($argv, 1);
if ($files === []) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root . '/app')
    );
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

$fixed = 0;
foreach ($files as $path) {
    $content = file_get_contents($path);
    if ($content === false || strlen($content) < 3) {
        continue;
    }
    if (substr($content, 0, 3) !== "\xEF\xBB\xBF") {
        continue;
    }
    file_put_contents($path, substr($content, 3));
    echo 'BOM removido: ' . $path . PHP_EOL;
    $fixed++;
}

echo "Total: {$fixed}" . PHP_EOL;
