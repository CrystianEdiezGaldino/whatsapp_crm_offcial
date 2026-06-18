#!/bin/bash
set -e
export DEBIAN_FRONTEND=noninteractive

echo "========================================"
echo "SQL Server 2022 Express - Installation"
echo "========================================"

echo ""
echo "1. Atualizando pacotes..."
apt-get update -y
apt-get upgrade -y

echo ""
echo "2. Adicionando repositorio Microsoft..."
curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | tee /etc/apt/trusted.gpg.d/microsoft.asc > /dev/null
curl -fsSL "https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/mssql-server-2022.list" | tee /etc/apt/sources.list.d/mssql-server-2022.list

echo ""
echo "3. Instalando SQL Server 2022 Express..."
apt-get update -y

export MSSQL_SA_PASSWORD='P@ssw0rd123!'
export ACCEPT_EULA='Y'
export MSSQL_EDITION='Express'

DEBIAN_FRONTEND=noninteractive apt-get install -y mssql-server

echo ""
echo "4. Iniciando SQL Server..."
systemctl start mssql-server
systemctl enable mssql-server
sleep 5

echo ""
echo "5. Instalando SQL Server Command-Line Tools..."
curl -fsSL "https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/prod.list" | tee /etc/apt/sources.list.d/msprod.list
apt-get update -y
ACCEPT_EULA=Y apt-get install -y mssql-tools18 unixodbc-dev

echo ""
echo "6. Aguardando SQL Server iniciar..."
sleep 15

echo ""
echo "7. Testando conexao..."
/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'P@ssw0rd123!' -C -Q "SELECT @@VERSION" || echo "Ainda inicializando..."

echo ""
echo "8. Criando banco de dados Whatsapp..."
/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'P@ssw0rd123!' -C <<'EOSQL'
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N'Whatsapp')
    CREATE DATABASE Whatsapp;
GO

USE Whatsapp;
GO

IF NOT EXISTS (SELECT name FROM sys.server_principals WHERE name = N'Php')
BEGIN
    CREATE LOGIN Php WITH PASSWORD = '$89%3a7';
END
GO

USE Whatsapp;
GO

IF NOT EXISTS (SELECT name FROM sys.database_principals WHERE name = N'Php')
BEGIN
    CREATE USER Php FOR LOGIN Php;
    ALTER ROLE db_owner ADD MEMBER Php;
END
GO

SELECT 'Database Whatsapp ready' AS Status;
GO
EOSQL

echo ""
echo "========================================"
echo "SQL Server 2022 Express instalado!"
echo "========================================"
