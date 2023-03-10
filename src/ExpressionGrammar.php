<?php


namespace AngelSourceLabs\LaravelExpressionGrammar;


use AngelSourceLabs\LaravelExpressionGrammar\Exceptions\GrammarNotDefinedForDatabaseException;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use PDO;

class ExpressionGrammar
{
    protected $value;
    protected $connection;
    protected $driver;
    protected $version;
    protected $resolved;

    public static function make()
    {
        return new ExpressionGrammar;
    }

    public function getVersionFromConnection(Connection $connection)
    {
        return $connection->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    public function connection(Connection $connection = null)
    {
        if (func_num_args() == 0) return $this->driver;

        $this->connection = $connection;
        $this->driver = $connection->getDriverName();
        $this->version = $this->getVersionFromConnection($connection);
        return $this;
    }

    public function driver($driver = null)
    {
        if (func_num_args() == 0) return $this->driver;

        $this->driver = $driver;
        return $this;
    }

    public function version($version = 0)
    {
        // Example version strings
        // MySQL: 8.0.31
        // MariaDB: 10.7.7-MariaDB-1:10.7.7+maria~ubu2004
        // Postgres: 14.6 (Debian 14.6-1.pgdg110+1)
        // SQLite: 3.40.0
        // SqlServer: 15.00.2000

        if (func_num_args() == 0) return $this->version;

        $this->version = $version;
        return $this;
    }

    public function mySql($string, $version = 0)
    {
        $this->grammar('mysql', $string, $version);
        return $this;
    }

    public function postgres($string, $version = 0)
    {
        $this->grammar('pgsql', $string, $version);
        return $this;
    }

    public function sqLite($string, $version = 0)
    {
        $this->grammar('sqlite', $string, $version);
        return $this;
    }

    public function sqlServer($string, $version = 0)
    {
        $this->grammar('sqlsrv', $string, $version);
        return $this;
    }

    public function grammar($driver, $string, $version = 0)
    {
        unset($this->resolved[$driver]);
        $this->value[$driver][$version] = $string;
        return $this;
    }

    public function __invoke($driver = null)
    {
        return $this->resolve($driver);
    }

    public function resolve($driverOrConnection = null, $version = null)
    {
        $driverOrConnection = $driverOrConnection ?? $this->driver ?? DB::connection();
        if ($driverOrConnection instanceof Connection) {
            $connection = $driverOrConnection;
            $driver = $connection->getDriverName();
            $version = $version ?? $this->getVersionFromConnection($connection);
        }
        else  {
            $driver = $driverOrConnection;
            $version = $version ?? $this->version ?? 0;
        }

        $driverMsg = $driver ?? "null";
        if (! isset($this->value[$driver])) throw new GrammarNotDefinedForDatabaseException("Grammar not defined for database driver {$driverMsg}\n" . print_r($this->value, true) );

        if (isset($this->resolved[$driver]) && isset($this->resolved[$driver][$version]))
            return $this->resolved[$driver][$version];

        uksort($this->value[$driver], "version_compare");
        $resolvedValue = null;
        foreach ($this->value[$driver] as $configuredVersion => $value) {
            if (version_compare($version, $configuredVersion) >= 0 /* && str_contains($versionMatch, $version) */)
                $resolvedValue = $value;
            else
                break;
        }
        if (! isset($resolvedValue)) throw new GrammarNotDefinedForDatabaseException("Grammar not defined for database version {$version}\n"  . print_r($this->value, true) );

        $this->resolved[$driver][$version] = $resolvedValue;

        return $resolvedValue;
    }

    public function expression($driver = null)
    {
        return App::makeWith(Expression::class, [$this->resolve($driver)]);
    }

    public function __toString()
    {
        return $this->resolve();
    }

}