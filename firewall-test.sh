#!/bin/bash

echo "========================================"
echo "Teste de Conectividade ao SQL Server"
echo "========================================"
echo ""
echo "De: $(hostname) ($(hostname -I | awk '{print $1}'))"
echo "Para: 192.168.1.6:1433"
echo ""

SQL_HOST="192.168.1.6"
SQL_PORT="1433"

echo "📋 TESTES:"
echo "──────────────────────────────────────"
echo ""

# 1. Ping
echo "1️⃣  Teste ICMP (Ping):"
if ping -c 2 -W 2 $SQL_HOST &>/dev/null; then
    echo "   ✅ PING OK - Host alcançável"
    PING_OK=1
else
    echo "   ❌ PING FALHOU - Host não responde"
    PING_OK=0
fi

echo ""

# 2. TCP Port
echo "2️⃣  Teste TCP (Porta 1433):"
if timeout 5 bash -c "cat < /dev/null > /dev/tcp/$SQL_HOST/$SQL_PORT" 2>/dev/null; then
    echo "   ✅ PORTA ABERTA - Conexão TCP possível"
    PORT_OK=1
else
    echo "   ❌ PORTA FECHADA/FILTRADA - Firewall bloqueando?"
    PORT_OK=0
fi

echo ""

# 3. Resumo
echo "="
echo "RESULTADO:"
echo "="
if [ $PING_OK -eq 1 ] && [ $PORT_OK -eq 1 ]; then
    echo "✅ TUDO OK - Rede funcionando, pode prosseguir com testes de BD"
elif [ $PING_OK -eq 1 ] && [ $PORT_OK -eq 0 ]; then
    echo "⚠️  FIREWALL BLOQUEANDO - Porta 1433 não está aberta"
    echo ""
    echo "📢 PRÓXIMO PASSO:"
    echo "   Peça ao admin de 192.168.1.6 para:"
    echo "   ✓ Abrir porta TCP 1433 para 192.168.255.5"
    echo "   ✓ Verificar firewall do Windows/Linux"
    echo "   ✓ Confirmar SQL Server listening em 192.168.1.6 (não 127.0.0.1)"
else
    echo "❌ REDE ISOLADA - 192.168.255.5 e 192.168.1.6 não conseguem se comunicar"
    echo ""
    echo "📢 PRÓXIMO PASSO:"
    echo "   Verificar roteamento de rede entre os dois subnets"
fi

echo ""
