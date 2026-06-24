-- Deletar usuario ti@santamonica.rec.br (rodar no SQL Server - banco Whatsapp)
USE Whatsapp;
GO

DECLARE @email NVARCHAR(255) = 'ti@santamonica.rec.br';
DECLARE @userId BIGINT;
DECLARE @fallbackId BIGINT;

SELECT @userId = id FROM users WHERE email = @email;

IF @userId IS NULL
BEGIN
    PRINT 'Usuario nao encontrado.';
    RETURN;
END

SELECT @fallbackId = TOP 1 id FROM users WHERE id <> @userId AND role IN ('admin', 'supervisor') ORDER BY id;

IF EXISTS (SELECT 1 FROM sys.tables WHERE name = 'conversation_flows') AND @fallbackId IS NOT NULL
    UPDATE conversation_flows SET created_by = @fallbackId WHERE created_by = @userId;

DELETE FROM users WHERE id = @userId;

PRINT 'Usuario deletado: ' + @email;
