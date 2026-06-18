# Conexão e Configuração do SQL Server no Laravel

Este documento descreve como funciona a conexão com o banco de dados Microsoft SQL Server no projeto **Agenda de Salão** (Laravel), bem como o passo a passo de como os drivers foram configurados e como realizar diagnósticos.

---

## 1. Informações de Conexão (Ambiente)

As credenciais e configurações de rede estão definidas no arquivo `.env`:

```env
DB_CONNECTION=sqlsrv
DB_HOST=192.168.1.6
DB_PORT=1433
DB_DATABASE=
DB_USERNAME=Php
DB_PASSWORD=$89%3a7
DB_TRUST_SERVER_CERTIFICATE=true
```

- **DB_CONNECTION**: Define `sqlsrv` como o driver de banco de dados padrão.
- **DB_TRUST_SERVER_CERTIFICATE**: Essencial para conexões locais em ambientes intranet onde o certificado SSL do SQL Server não é emitido por uma autoridade certificadora pública reconhecida.

---

## 2. Arquitetura de Drivers do PHP para SQL Server

Para que o PHP se comunique com o Microsoft SQL Server, são necessárias duas extensões principais:

1. **`sqlsrv`**: Driver nativo que oferece funções procedimentais (`sqlsrv_connect`, `sqlsrv_query`, etc.). Utilizado em scripts de teste e legados.
2. **`pdo_sqlsrv`**: Driver PDO que implementa a interface do PHP Data Objects, permitindo que o Eloquent ORM do Laravel interaja com o banco de dados.

Ambos dependem do driver do sistema operacional: **Microsoft ODBC Driver for SQL Server**.

---

## 3. Passo a Passo de Configuração dos Drivers no Windows/WAMP

Caso precise reinstalar ou atualizar os drivers (por exemplo, ao alterar a versão do PHP), siga as instruções abaixo:

### Passo 1: Verificar a Versão do PHP e Arquitetura

Abra o prompt de comando ou terminal e execute:

```bash
php -v
php -i | findstr -i "architecture"
php -i | findstr -i "thread"
```

_Nota: Identifique se o PHP é **x64** ou **x86** e se é **Thread Safe (TS)** ou **Non-Thread Safe (NTS)**. O padrão do WAMP costuma ser Thread Safe._

### Passo 2: Baixar os Drivers Corretos

Acesse o site oficial da Microsoft:
[Download Microsoft Drivers for PHP for SQL Server](https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server)

- Para **PHP 8.2**: Baixe a versão `5.11` (Microsoft Drivers 5.11 for PHP for SQL Server).
- Para **PHP 8.4**: Requer a versão `5.12` ou superior.

### Passo 3: Extrair e Copiar as DLLs

1. Execute o arquivo autoextraível baixado (`.exe`).
2. Extraia os arquivos para um diretório temporário.
3. Localize as DLLs correspondentes à sua versão do PHP. Por exemplo, para **PHP 8.2 (Thread Safe, x64)**:
    - `php_sqlsrv_82_ts_x64.dll`
    - `php_pdo_sqlsrv_82_ts_x64.dll`
4. Copie as DLLs para a pasta de extensões do PHP no WAMP (exemplo):
    ```
    C:\wamp64\bin\php\php8.2.26\ext\
    ```

### Passo 4: Habilitar as Extensões no `php.ini`

Edite o arquivo `php.ini` ativo (você pode localizá-lo via WAMP ou rodando `php --ini`). Adicione as seguintes linhas na seção de extensões:

```ini
extension=php_sqlsrv_82_ts_x64.dll
extension=php_pdo_sqlsrv_82_ts_x64.dll
```

### Passo 5: Instalar o Driver ODBC no Windows

Se ainda não estiver instalado na máquina servidora, instale o **Microsoft ODBC Driver for SQL Server** (versão 17 ou 18), disponível na página oficial da Microsoft. Sem este driver no Windows, a DLL do PHP retornará erro de inicialização.

### Passo 6: Reiniciar o Servidor Web

Reinicie todos os serviços do WAMP (Apache/Nginx e PHP).

---

## 4. Configuração no Laravel (`config/database.php`)

No Laravel, a conexão do SQL Server está mapeada sob a chave `sqlsrv` em `connections`:

```php
'sqlsrv' => [
    'driver' => 'sqlsrv',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', '1433'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'encrypt' => env('DB_ENCRYPT', 'yes'),
    'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'true'),
],
```

---

## 5. Ferramentas de Diagnóstico e Teste

O projeto inclui scripts dedicados a diagnosticar e isolar problemas de conexão sem depender do Laravel.

### Script 1: Diagnóstico Completo (`diagnostico_sqlsrv.php`)

Este script exibe detalhes do sistema operacional, extensões carregadas, acessibilidade de rede e tenta queries tanto com o driver nativo quanto com o PDO.

Execute no terminal:

```bash
php diagnostico_sqlsrv.php
```

### Script 2: Teste de Conexão Rápido (`test_sqlsrv_connection.php`)

Verifica se as extensões estão carregadas, lê as credenciais do arquivo `.env` local e executa consultas básicas na base de dados.

Execute no terminal:

```bash
php test_sqlsrv_connection.php
```

---

## 6. Solução de Problemas Comuns (Troubleshooting)

### Erro: `Could not find driver` no Laravel

- **Causa**: A extensão `pdo_sqlsrv` não está habilitada ou o arquivo DLL correto não foi encontrado.
- **Solução**: Verifique se a linha `extension=...` está no `php.ini` correto e se a DLL está fisicamente na pasta `ext/`.

### Erro: `SQLSTATE [08001]: [Microsoft][ODBC Driver 17 for SQL Server]`

- **Causa**: Erro de rede ou o SQL Server não está aceitando conexões TCP/IP na porta 1433.
- **Solução**:
    1. No servidor SQL Server, abra o **SQL Server Configuration Manager**.
    2. Vá em _SQL Server Network Configuration_ > _Protocols for MSSQLSERVER_.
    3. Garanta que o protocolo **TCP/IP** está **Enabled**.
    4. Nas propriedades do TCP/IP, aba _IP Addresses_, verifique se o IP do servidor e a porta `1433` estão configurados.
    5. Reinicie o serviço do SQL Server.

### Erro de TLS / SSL Handshake

- **Causa**: O driver mais recente do SQL Server exige criptografia por padrão.
- **Solução**: Adicione `'trust_server_certificate' => true` nas configurações ou `DB_TRUST_SERVER_CERTIFICATE=true` no `.env`.
