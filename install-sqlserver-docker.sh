#!/bin/bash
set -e
export DEBIAN_FRONTEND=noninteractive

echo "=== SQL Server via Docker (Ubuntu 24.04) ==="

apt-get update -y
apt-get install -y docker.io
systemctl enable --now docker

SA_PASS='P@ssw0rd123!'
docker pull mcr.microsoft.com/mssql/server:2022-latest

docker rm -f mssql-whatsapp 2>/dev/null || true
docker run -d --name mssql-whatsapp \
  -e ACCEPT_EULA=Y \
  -e MSSQL_SA_PASSWORD="$SA_PASS" \
  -e MSSQL_PID=Express \
  -p 1433:1433 \
  --restart unless-stopped \
  mcr.microsoft.com/mssql/server:2022-latest

echo "Aguardando SQL Server..."
for i in $(seq 1 30); do
  if docker exec mssql-whatsapp /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$SA_PASS" -C -Q "SELECT 1" &>/dev/null; then
    echo "SQL Server pronto!"
    break
  fi
  sleep 3
done

docker exec mssql-whatsapp /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$SA_PASS" -C -Q "
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N'Whatsapp') CREATE DATABASE Whatsapp;
"

docker exec mssql-whatsapp /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$SA_PASS" -C -Q "
IF NOT EXISTS (SELECT name FROM sys.server_principals WHERE name = N'Php')
  CREATE LOGIN Php WITH PASSWORD = '\$89%3a7';
"

docker exec mssql-whatsapp /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$SA_PASS" -C -d Whatsapp -Q "
IF NOT EXISTS (SELECT name FROM sys.database_principals WHERE name = N'Php')
BEGIN
  CREATE USER Php FOR LOGIN Php;
  ALTER ROLE db_owner ADD MEMBER Php;
END
"

echo "=== SQL Server Docker OK na porta 1433 ==="
