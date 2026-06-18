IF NOT EXISTS (SELECT name FROM sys.server_principals WHERE name = N'Php')
    CREATE LOGIN Php WITH PASSWORD = '$89%3a7', CHECK_POLICY = OFF;
GO
USE Whatsapp;
GO
IF NOT EXISTS (SELECT name FROM sys.database_principals WHERE name = N'Php')
BEGIN
    CREATE USER Php FOR LOGIN Php;
    ALTER ROLE db_owner ADD MEMBER Php;
END
GO
