#!/bin/bash

echo "======================================"
echo "SQL Server Connectivity Diagnostic"
echo "======================================"
echo ""

SQL_HOST="192.168.1.6"
SQL_PORT="1433"

echo "1️⃣  Check if host $SQL_HOST is reachable (ping)..."
if ping -c 1 -W 2 $SQL_HOST > /dev/null 2>&1; then
    echo "   ✅ Host is reachable"
else
    echo "   ❌ Host is NOT reachable - Network problem?"
    echo "   Run: ping $SQL_HOST"
fi

echo ""
echo "2️⃣  Check if port $SQL_PORT is open on $SQL_HOST..."
if timeout 3 bash -c "cat < /dev/null > /dev/tcp/$SQL_HOST/$SQL_PORT" 2>/dev/null; then
    echo "   ✅ Port $SQL_PORT is OPEN"
else
    echo "   ❌ Port $SQL_PORT is CLOSED or FILTERED"
    echo "   Check: Is SQL Server running? Is port 1433 correct?"
fi

echo ""
echo "3️⃣  Checking current PHP sqlsrv driver..."
php -m 2>/dev/null | grep sqlsrv
if [ $? -eq 0 ]; then
    echo "   ✅ sqlsrv driver is installed"
else
    echo "   ❌ sqlsrv driver is NOT installed"
    echo "   Run: sudo pecl install sqlsrv pdo_sqlsrv"
fi

echo ""
echo "4️⃣  Checking MySQL connectivity (alternative test)..."
mysql -h 192.168.1.6 -u root -e "SELECT 1" 2>&1 | grep -q "1" && echo "   ✅ MySQL works" || echo "   ⚠️  MySQL not accessible (expected if using SQL Server)"

echo ""
echo "======================================"
echo "NEXT STEPS:"
echo "======================================"
echo ""
echo "If host is not reachable:"
echo "  → Check network connectivity between servers"
echo "  → Check DNS resolution: nslookup 192.168.1.6"
echo "  → Check firewall: sudo ufw status"
echo ""
echo "If port is closed:"
echo "  → Check if SQL Server is running"
echo "  → Check if listening on port 1433: netstat -tlnp | grep 1433"
echo "  → Check SQL Server logs"
echo ""
echo "If sqlsrv driver missing:"
echo "  → Install ODBC: sudo apt-get install msodbcsql18"
echo "  → Install driver: sudo pecl install sqlsrv pdo_sqlsrv"
echo ""
