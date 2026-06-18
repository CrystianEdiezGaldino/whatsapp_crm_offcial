#!/bin/bash

echo "========================================"
echo "Network Diagnostic from Linux Server"
echo "========================================"
echo ""

SQL_HOST="192.168.1.6"
SQL_PORT="1433"

echo "🖥️  Current Server Information:"
echo "  Hostname: $(hostname)"
echo "  IP addresses:"
hostname -I | tr ' ' '\n' | sed 's/^/    /'
echo ""

echo "🌐 Network Tests to $SQL_HOST:$SQL_PORT"
echo "──────────────────────────────────────"
echo ""

echo "1️⃣  Can we ping the SQL Server? (ICMP)"
if ping -c 2 -W 3 $SQL_HOST 2>/dev/null; then
    echo "   ✅ PING OK - Host is reachable"
else
    echo "   ❌ PING FAILED - Host is not reachable"
fi

echo ""
echo "2️⃣  Can we connect to port 1433? (TCP)"
if timeout 5 bash -c "cat < /dev/null > /dev/tcp/$SQL_HOST/$SQL_PORT" 2>/dev/null; then
    echo "   ✅ TCP PORT 1433 OPEN - Can connect!"
else
    echo "   ❌ TCP PORT 1433 CLOSED/FILTERED"
fi

echo ""
echo "3️⃣  Network route to $SQL_HOST"
echo "   Route:"
traceroute -m 5 $SQL_HOST 2>/dev/null || echo "   (traceroute not available)"

echo ""
echo "4️⃣  Check if SQL Server is on localhost instead"
echo "   Trying to connect to localhost:1433..."
if timeout 5 bash -c "cat < /dev/null > /dev/tcp/127.0.0.1/$SQL_PORT" 2>/dev/null; then
    echo "   ✅ SQL Server is on LOCALHOST (127.0.0.1)!"
else
    echo "   ❌ SQL Server not on localhost"
fi

echo ""
echo "   Trying to connect to current hostname:1433..."
CURRENT_IP=$(hostname -I | awk '{print $1}')
if timeout 5 bash -c "cat < /dev/null > /dev/tcp/$CURRENT_IP/$SQL_PORT" 2>/dev/null; then
    echo "   ✅ SQL Server is on CURRENT SERVER ($CURRENT_IP)!"
else
    echo "   ❌ SQL Server not on current server"
fi

echo ""
echo "5️⃣  Check DNS resolution"
echo "   Resolving 192.168.1.6..."
nslookup 192.168.1.6 2>/dev/null || dig -x 192.168.1.6 2>/dev/null || echo "   (DNS tools not available)"

echo ""
echo "6️⃣  Open ports on current server"
echo "   Looking for SQL Server on port 1433..."
sudo netstat -tlnp 2>/dev/null | grep -i 1433 || echo "   (No process listening on 1433)"

echo ""
echo "========================================"
echo "POSSIBLE ISSUES:"
echo "========================================"
echo ""
echo "If ping fails:"
echo "  ❌ Network is isolated or blocked by firewall"
echo ""
echo "If port 1433 is closed but ping works:"
echo "  ❌ Firewall/iptables is blocking the port"
echo "  💡 Ask server admin to open port 1433 from 192.168.255.5 to 192.168.1.6"
echo ""
echo "If localhost:1433 works:"
echo "  💡 SQL Server is on the SAME server!"
echo "  💡 Update .env: DB_HOST=127.0.0.1 (or localhost)"
echo ""
echo "========================================"
echo ""
