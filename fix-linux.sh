#!/bin/bash

echo "================================"
echo "Fixing Laravel on Linux"
echo "================================"
echo ""

cd /var/www/smcc-whatsapp

# Make sure we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ artisan file not found. Wrong directory?"
    exit 1
fi

# Web server user (Apache/Nginx on Debian/Ubuntu)
WWW_USER="${WWW_USER:-www-data}"

echo "0️⃣  Corrigindo permissões de storage e bootstrap/cache..."
if id "$WWW_USER" &>/dev/null; then
    sudo chown -R "$WWW_USER:$WWW_USER" storage bootstrap/cache
    sudo chmod -R ug+rwx storage bootstrap/cache
    sudo find storage bootstrap/cache -type d -exec chmod g+s {} \;
    echo "   ✅ Permissões ajustadas para $WWW_USER"
else
    echo "   ⚠️  Usuário $WWW_USER não encontrado. Tente: WWW_USER=apache sudo -E bash fix-linux.sh"
fi

echo ""
php artisan config:clear
if [ $? -eq 0 ]; then echo "   ✅ Done"; else echo "   ❌ Failed"; fi

echo ""
echo "2️⃣  Clearing application cache..."
php artisan cache:clear
if [ $? -eq 0 ]; then echo "   ✅ Done"; else echo "   ❌ Failed"; fi

echo ""
echo "3️⃣  Clearing view cache..."
php artisan view:clear
if [ $? -eq 0 ]; then echo "   ✅ Done"; else echo "   ❌ Failed"; fi

echo ""
echo "4️⃣  Testing database connection..."
php -r "
require 'bootstrap/app.php';
\$app = \Illuminate\Foundation\Application::getInstance();
\$db = \$app['db'];
try {
    \$db->connection('sqlsrv')->getPdo();
    echo '   ✅ Database connection successful!';
} catch (Exception \$e) {
    echo '   ❌ Database connection failed: ' . \$e->getMessage();
}
"

echo ""
echo ""
echo "5️⃣  Restarting Apache..."
sudo systemctl restart apache2 2>/dev/null || sudo service apache2 restart

if [ $? -eq 0 ]; then
    echo "   ✅ Apache restarted"
else
    echo "   ⚠️  Could not restart Apache (run: sudo systemctl restart apache2)"
fi

echo ""
echo "================================"
echo "✅ Fix completed!"
echo "================================"
echo ""
echo "Now test the login:"
echo "  http://192.168.255.5/smcc-whatsapp/public/login"
echo ""
