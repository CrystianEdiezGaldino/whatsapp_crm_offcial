#!/bin/bash

set -e

echo "========================================"
echo "SQL Server 2022 Express - Installation"
echo "========================================"
echo ""
echo "⚠️  Este script vai instalar SQL Server no sistema"
echo "Pode levar 10-15 minutos"
echo ""
read -p "Continuar? (s/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    exit 1
fi

echo ""
echo "1️⃣  Atualizando pacotes..."
sudo apt-get update -y
sudo apt-get upgrade -y

echo ""
echo "2️⃣  Adicionando repositório Microsoft..."
curl https://packages.microsoft.com/keys/microsoft.asc | sudo tee /etc/apt/trusted.gpg.d/microsoft.asc > /dev/null
sudo add-apt-repository "$(curl https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/mssql-server-2022.list)"

echo ""
echo "3️⃣  Instalando SQL Server 2022 Express..."
sudo apt-get update -y

# SQL Server 2022 Express
export MSSQL_SA_PASSWORD='P@ssw0rd123!' # MUDE ISTO!
export ACCEPT_EULA='Y'
export MSSQL_EDITION='Express'

sudo -E apt-get install -y mssql-server

echo ""
echo "4️⃣  Iniciando SQL Server..."
sudo systemctl start mssql-server
sudo systemctl enable mssql-server

sleep 5

echo ""
echo "5️⃣  Instalando SQL Server Command-Line Tools..."
curl https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/prod.list | sudo tee /etc/apt/sources.list.d/msprod.list
sudo apt-get update -y
sudo ACCEPT_EULA=Y apt-get install -y mssql-tools18 unixodbc-dev

# Adicionar ao PATH
echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> ~/.bashrc
source ~/.bashrc

echo ""
echo "6️⃣  Aguardando SQL Server iniciar..."
sleep 10

echo ""
echo "7️⃣  Testando conexão..."
/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'P@ssw0rd123!' -C -Q "SELECT @@VERSION" || echo "⚠️  Ainda inicializando..."

echo ""
echo "8️⃣  Criando banco de dados Whatsapp..."
/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'P@ssw0rd123!' -C << EOF
CREATE DATABASE Whatsapp;
GO

USE Whatsapp;
GO

-- Criar usuário Php
CREATE LOGIN Php WITH PASSWORD = '\$89%3a7';
GO

CREATE USER Php FOR LOGIN Php;
GO

ALTER ROLE db_owner ADD MEMBER Php;
GO

-- Tabela de teste
CREATE TABLE TestTable (
    Id INT PRIMARY KEY IDENTITY(1,1),
    Name NVARCHAR(100)
);

INSERT INTO TestTable (Name) VALUES ('Test');
GO

SELECT 'Database Whatsapp created successfully!' AS Status;
EOF

echo ""
echo "========================================"
echo "✅ SQL Server 2022 Express instalado!"
echo "========================================"
echo ""
echo "📋 Informações de Conexão:"
echo "  Host: localhost ou 127.0.0.1"
echo "  Port: 1433"
echo "  Database: Whatsapp"
echo "  Username (admin): sa"
echo "  Password (admin): P@ssw0rd123!"
echo "  Username (app): Php"
echo "  Password (app): \$89%3a7"
echo ""
echo "🔄 Próximos passos:"
echo "  1. Atualizar .env:"
echo "     DB_HOST=127.0.0.1"
echo "  2. Fazer cache clear:"
echo "     php artisan config:clear"
echo "  3. Testar login:"
echo "     http://192.168.255.5/smcc-whatsapp/public/login"
echo ""
