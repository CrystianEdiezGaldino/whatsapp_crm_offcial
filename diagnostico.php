<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Diagnóstico do Servidor</h2>";
echo "<pre>";
echo "Diretório atual (__DIR__): " . __DIR__ . "\n";
echo "Diretório de execução (getcwd): " . getcwd() . "\n";
echo "Sistema Operacional: " . php_uname() . "\n";
echo "Usuário do Apache: " . get_current_user() . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "\n--- Listando /etc/apache2/sites-available ---\n";

$output = shell_exec('ls -la /etc/apache2/sites-available/ 2>&1');
echo $output ? $output : "Sem permissão ou comando não encontrado\n";

echo "\n--- Arquivo httpd.conf ou apache2.conf ---\n";
$apache_conf = file_exists('/etc/apache2/apache2.conf') ? file_get_contents('/etc/apache2/apache2.conf') : 'Não encontrado';
echo substr($apache_conf, 0, 1000) . "...\n";

echo "\n--- Testando Rewrite Module ---\n";
echo extension_loaded('mod_rewrite') ? "mod_rewrite: ATIVO\n" : "mod_rewrite: INATIVO\n";

echo "\n--- Arquivo .htaccess ---\n";
$htaccess = file_get_contents(__DIR__ . '/.htaccess');
echo $htaccess . "\n";

echo "</pre>";
?>
