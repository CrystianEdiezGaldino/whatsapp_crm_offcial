#!/bin/bash

set -e

echo "========================================"
echo "Instalando SSL/TLS com Let's Encrypt"
echo "========================================"
echo ""
echo "Domínio: servicos2.santamonica.rec.br"
echo ""

DOMAIN="servicos2.santamonica.rec.br"

echo "1️⃣  Instalando Certbot..."
sudo apt-get update -y
sudo apt-get install -y certbot python3-certbot-apache

echo ""
echo "2️⃣  Gerando certificado para $DOMAIN..."
sudo certbot certonly --apache \
    -d $DOMAIN \
    -d www.$DOMAIN \
    --agree-tos \
    -m admin@santamonica.rec.br \
    --preferred-challenges http \
    --non-interactive

echo ""
echo "3️⃣  Verificando certificado..."
sudo certbot certificates

echo ""
echo "4️⃣  Configurando Apache para HTTPS..."
sudo tee /etc/apache2/sites-available/smcc-whatsapp-ssl.conf > /dev/null << 'APACHE'
<VirtualHost *:443>
    ServerName servicos2.santamonica.rec.br
    ServerAlias www.servicos2.santamonica.rec.br

    DocumentRoot /var/www/smcc-whatsapp/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/servicos2.santamonica.rec.br/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/servicos2.santamonica.rec.br/privkey.pem

    <Directory /var/www/smcc-whatsapp/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted

        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteRule ^index\.html$ - [L]
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule . /index.php [L]
        </IfModule>
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/smcc-whatsapp-error.log
    CustomLog ${APACHE_LOG_DIR}/smcc-whatsapp-access.log combined
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName servicos2.santamonica.rec.br
    ServerAlias www.servicos2.santamonica.rec.br

    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>
APACHE

echo ""
echo "5️⃣  Habilitando site com SSL..."
sudo a2ensite smcc-whatsapp-ssl.conf
sudo a2enmod ssl
sudo a2enmod rewrite
sudo a2enmod proxy_fcgi

echo ""
echo "6️⃣  Testando configuração Apache..."
sudo apache2ctl configtest

echo ""
echo "7️⃣  Reiniciando Apache..."
sudo systemctl restart apache2

echo ""
echo "8️⃣  Configurando renovação automática de certificado..."
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer

echo ""
echo "========================================"
echo "✅ SSL/TLS Configurado com Sucesso!"
echo "========================================"
echo ""
echo "📋 Informações:"
echo "  Domínio: https://servicos2.santamonica.rec.br/smcc-whatsapp/public"
echo "  Certificado: /etc/letsencrypt/live/servicos2.santamonica.rec.br/"
echo "  Renovação automática: Habilitada"
echo ""
echo "🔗 Teste agora:"
echo "  https://servicos2.santamonica.rec.br/smcc-whatsapp/public/login"
echo ""
