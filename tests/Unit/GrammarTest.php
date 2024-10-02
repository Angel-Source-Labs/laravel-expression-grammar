<?php

namespace Tests\Unit;


use AngelSourceLabs\LaravelExpressionGrammar\ExpressionGrammar;
use Illuminate\Support\Facades\DB;

class GrammarTest extends BaseTestCase
{
    use DatabaseConnections;

    const MYSQL_STRING = 'select * from `users` where grammar = "mysql" and price > IF(state = "TX", 1, 0)';
    const POSTGRES_STRING = 'select * from "users" where grammar = "pgsql" and price > IF(state = "TX", 1, 0)';
    const SQLITE_STRING = 'select * from "users" where grammar = "sqlite" and price > IF(state = "TX", 1, 0)';
    const SQLSERVER_STRING = 'select * from [users] where grammar = "sqlserver" and price > IF(state = "TX", 1, 0)';

    public function assertGrammar($sql)
    {
        $grammar = ExpressionGrammar::make()
            ->mySql(self::MYSQL_STRING)
            ->postgres(self::POSTGRES_STRING)
            ->sqLite(self::SQLITE_STRING)
            ->sqlServer(self::SQLSERVER_STRING);

        $this->assertEquals($sql, $grammar->resolve());
    }

    /**
     * @environment-setup useMySqlConnection
     */
    public function test_MySqlConnection_resolves_correct_grammar()
    {
        $this->assertGrammar(self::MYSQL_STRING);
    }

    /**
     * @environment-setup useMariaDbConnection
     */
    public function test_MariaDb_resolves_correct_grammar()
    {
        $this->assertGrammar(self::MYSQL_STRING);
    }

    /**
     * @environment-setup usePostgresConnection
     */
    public function test_PostgresConnection_resolves_correct_grammar()
    {
        $this->markTestIncomplete(print_r(config('database.connections')));
        $this->assertGrammar(self::POSTGRES_STRING);
    }

    /**
     * @environment-setup useSQLiteConnection
     */
    public function test_useSQLiteConnection_resolves_correct_grammar()
    {
        $this->assertGrammar(self::SQLITE_STRING);
    }

    /**
     * @environment-setup useSqlServerConnection
     */
    public function test_useSqlServerConnection_resolves_correct_grammar()
    {
        try {
            $this->assertGrammar(self::SQLSERVER_STRING);
        }
        catch (\Exception $e) {
            if ($e->getCode() == 'IM001') $this->markTestSkipped("Test Marked as skipped because SQLServer requires proper environment setup to run.\nSQLServer error information: " . $e->getMessage() . "\n");
        }
    }

    protected function grammarValue($driver = "mysql", $version = 0) {
        return 'grammar = "' . $driver . '" and version = ' . $version;
    }

    public function test_mariadb_version_resolve()
    {
        $this->markTestIncomplete();
        $grammar = ExpressionGrammar::make()
            ->mySql($this->grammarValue("mysql"))
            ->mySql($this->grammarValue("mysql", "8.0"), "8.0")
            ->mariaDb($this->grammarValue("MariaDB"));

        $grammar->driver("mysql");

        $grammar->version(("5.7"));
        $this->assertEquals($this->grammarValue("mysql"), $grammar->resolve());
        $grammar->version(("8.0"));
        $this->assertEquals($this->grammarValue("mysql", "8.0"), $grammar->resolve());
        $grammar->version(("10.7.7-MariaDB"));
        $this->assertEquals($this->grammarValue("MariaDB"), $grammar->resolve());
    }

    public function test_grammar_versions_resolve()
    {
        // MySQL: 8.0.31
        // MariaDB: 10.7.7-MariaDB-1:10.7.7+maria~ubu2004
        // Postgres: 14.6 (Debian 14.6-1.pgdg110+1)
        // SQLite: 3.40.0
        // SqlServer: 15.00.2000

        $grammar = ExpressionGrammar::make()
            ->mySql($this->grammarValue("mysql"))
            ->postgres($this->grammarValue('pgsql'));

        foreach(['mysql', 'pgsql'] as $driver)
            foreach(['2.0', '1.1', '1.0'] as $version)
                $grammar->grammar($driver, $this->grammarValue($driver, $version), $version);

        foreach(['mysql', 'pgsql'] as $driver) {
            $this->assertEquals($this->grammarValue($driver, "2.0"), $grammar->resolve($driver, '2.0'));
            $this->assertEquals($this->grammarValue($driver, "2.0"), $grammar->resolve($driver, '2.1'));
            $this->assertEquals($this->grammarValue($driver, "2.0"), $grammar->resolve($driver, '3.1'));

            $this->assertEquals($this->grammarValue($driver, "1.1"), $grammar->resolve($driver, '1.1'));
            $this->assertEquals($this->grammarValue($driver, "1.1"), $grammar->resolve($driver, '1.2'));
            $this->assertEquals($this->grammarValue($driver, "1.1"), $grammar->resolve($driver, '1.10'));

            $this->assertEquals($this->grammarValue($driver, "1.0"), $grammar->resolve($driver, '1.0'));

            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve($driver, '0'));
            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve($driver, '0.0'));
            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve($driver, '0.1'));
            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve($driver, '0.2'));
            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve($driver, '0.10'));
        }
    }

    public function test_resolve_grammar_without_parameters()
    {
        $grammar = ExpressionGrammar::make()
            ->mySql($this->grammarValue("mysql"))
            ->postgres($this->grammarValue('pgsql'));


        foreach(['mysql', 'pgsql'] as $driver) {
            $grammar->driver($driver)->version("4.10");
            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve());
        }
    }

}










