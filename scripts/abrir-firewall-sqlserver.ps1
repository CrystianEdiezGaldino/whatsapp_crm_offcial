# Executar NO SQL SERVER (192.168.1.6) como Administrador via RDP

# 1. Liberar firewall para o servidor web
New-NetFirewallRule -DisplayName "SMCC WhatsApp Web" `
    -Direction Inbound -Protocol TCP -LocalPort 1433 `
    -RemoteAddress 192.168.255.5 -Action Allow -Profile Any

# 2. Confirmar que SQL Server aceita conexoes TCP
Get-NetFirewallRule -DisplayName "*SQL*" | Where-Object { $_.Enabled -eq 'True' } | Select DisplayName, Direction

# 3. Testar listener na porta 1433
Get-NetTCPConnection -LocalPort 1433 -State Listen -ErrorAction SilentlyContinue

Write-Host "`nDepois, teste do servidor web:"
Write-Host "  http://192.168.255.5/smcc-whatsapp/test_network.php"
