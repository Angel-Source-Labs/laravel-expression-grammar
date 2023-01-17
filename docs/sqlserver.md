# MacOS ODBC installation
Installation instructions for odbc driver for sql-server on MacOS are at this link:
* https://learn.microsoft.com/en-us/sql/connect/odbc/linux-mac/install-microsoft-odbc-driver-sql-server-macos?view=sql-server-ver16

In summary, after you have homebrew installed, run the following commands:
```shell
brew tap microsoft/mssql-release https://github.com/Microsoft/homebrew-mssql-release
brew update
HOMEBREW_ACCEPT_EULA=Y brew install msodbcsql18 mssql-tools18
```
## PHP pdo_sqlsrv extension installation
After installing the ODBC drivers, run the following command to install the PDO extension:
```shell
sudo CXXFLAGS="-I/opt/homebrew/opt/unixodbc/include/" LDFLAGS="-L/opt/homebrew/lib/" pecl install pdo_sqlsrv
```

Verify that the following lines were added to php.ini by the `pecl install pdo_sqlsrv` command
```ini
extension="pdo_sqlsrv. so"
extension="sqlsrv.so"
```

### Troubleshooting
If you initially installed 17, and then uninstalled it with `brew uninstall msodbcsql17` you might get this error:   
`PDOException : SQLSTATE[01000]: [unixODBC][Driver Manager]Can't open lib '/opt/homebrew/lib/libmsodbcsql.17.dylib' : file not found`

To resolve, follow the instructions in `brew info msodbcsql17`
```
 ==> Caveats
If you installed this formula with the registration option (default), you'll
need to manually remove [ODBC Driver 17 for SQL Server] section from
odbcinst.ini after the formula is uninstalled. This can be done by executing
the following command:

odbcinst -u -d -n "ODBC Driver 17 for SQL Server"
```

## Docker container
The Microsoft aure-sql-edge docker image is an image that can run on Mac M1 machines.

Setup sqlserver in docker-compose.yml similar to below.
* ACCEPT_EULA must be 'Y' to accept the license agreement
* MSSQL_SA_PASSWORD must meet minimum password requirements.  'password' will not work.
  * At least 8 characters including uppercase, lowercase letters, base-10 digits and/or non-alphanumeric symbols

See https://hub.docker.com/_/microsoft-azure-sql-edge for more information.

```yaml
  sqlserver:
    platform: linux/arm64/v8
    image: mcr.microsoft.com/azure-sql-edge
    ports:
      - "14330:1433"
    environment:
      ACCEPT_EULA: 'Y'
      MSSQL_SA_PASSWORD: 'MyPass@word'
      MSSQL_PID: 'Developer'
      MSSQL_USER: 'SA'
```

## Configure the SQL Server connection in Laravel
> ODBC Driver 18 for SQL Server and JDBC Driver 10.2 for SQL Server both default to the Encrypt=yes; connection string option with the goal of improving data security for SQL Server users and developers. Unfortunately, by default, SQL Server instances are installed with self-signed X.509 certificate that are not trusted by any CAs (Certificate Authorities) so most people upgrading to these versions of the drivers are receiving errors similar to yours.
>
> You have three options here:
>
> 1. Export the public key portion of the target SQL Server's certificate to a .crt file and import it into the trusted certificate stores of the users that need to connect to it, or
> 2. Add TrustServerCertificate=true; (or TrustServerCertificate=yes;, depending on your programming language) to your connection string, so that the server's certificate is accepted without error and allows secure, encrypted connections, or
> 3. Add Encrypt=false; (or Encrypt=no;, depending on your programming language) to your connection string. This is the least desirable option as it prevents the connection from being encrypted, meaning that the traffic between your SQL Server and the client can be intercepted and inspected with ease.

Modify `config/database.php` to add the `encrypt`, `odbc_driver`, and `trust_server_certificate` fields:
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
            'odbc_driver' => '{ODBC Driver 18 for SQL Server}'
            'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],
```

Configure your `.env` file appropriately.  These settings match the docker-compose settings above.
```shell
DB_HOST=localhost
DB_PORT=14330
DB_USERNAME=SA
DB_PASSWORD=MyPass@word
DB_DATABASE=
DB_ENCRYPT=yes
DB_TRUST_SERVER_CERTIFICATE=true
```

