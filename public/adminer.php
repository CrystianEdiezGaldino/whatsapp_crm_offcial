<?php
/**
 * Adminer com TrustServerCertificate (patch em adminer-core.php)
 */
function adminer_object()
{
    class AdminerMsSqlHint extends Adminer
    {
        function loginForm()
        {
            parent::loginForm();
            echo '<p style="margin:1em 0;padding:.75em;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;font-size:13px">';
            echo '<strong>SQL Server local</strong><br>';
            echo 'Sistema: <code>MS SQL (beta)</code> ? Servidor: <code>127.0.0.1</code> ? Banco: <code>Whatsapp</code><br>';
            echo 'Usu?rio app: <code>Php</code> ? SA: <code>sa</code> / <code>P@ssw0rd123!</code>';
            echo '</p>';
        }
    }

    return new AdminerMsSqlHint();
}

include __DIR__ . '/adminer-core.php';
