#!/bin/bash

echo "================================"
echo "Installing SQL Server PHP Driver"
echo "================================"

# Update package manager
sudo apt-get update -y

# Install ODBC Driver 18 for SQL Server (if not already installed)
if ! which sqlsrv > /dev/null 2>&1; then
    echo "Installing Microsoft ODBC Driver 18 for SQL Server..."
    curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
    curl https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/prod.list > /etc/apt/sources.list.d/mssql-release.list
    sudo apt-get update
    sudo ACCEPT_EULA=Y apt-get install -y msodbcsql18
fi

# Install PHP development headers and build tools
echo "Installing PHP development tools..."
sudo apt-get install -y php-dev build-essential

# Install PHP sqlsrv and pdo_sqlsrv extensions via PECL
echo "Installing PHP sqlsrv extension..."
sudo pecl install sqlsrv

echo "Installing PHP pdo_sqlsrv extension..."
sudo pecl install pdo_sqlsrv

# Find PHP ini files and add extensions
PHP_INI_DIR=$(php -r "echo dirname(php.ini_loaded_file());")
echo "PHP config directory: $PHP_INI_DIR"

# Add extensions to ini files
if [ -d "$PHP_INI_DIR/conf.d" ]; then
    echo "extension=sqlsrv.so" | sudo tee "$PHP_INI_DIR/conf.d/20-sqlsrv.ini" > /dev/null
    echo "extension=pdo_sqlsrv.so" | sudo tee "$PHP_INI_DIR/conf.d/20-pdo_sqlsrv.ini" > /dev/null
else
    echo "extension=sqlsrv.so" | sudo tee -a "$PHP_INI_DIR/php.ini" > /dev/null
    echo "extension=pdo_sqlsrv.so" | sudo tee -a "$PHP_INI_DIR/php.ini" > /dev/null
fi

# Verify installation
echo ""
echo "Verifying installation..."
php -m | grep sqlsrv

# Restart Apache
echo "Restarting Apache..."
sudo systemctl restart apache2 || sudo service apache2 restart

echo ""
echo "================================"
echo "Installation completed!"
echo "================================"
