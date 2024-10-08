<?php


namespace Tests\Unit;


trait DatabaseConnections
{
    public function useMySqlConnection($app)
    {
        config(['database.default' => 'mysql']);
        $app['config']->set('database.connections.mysql.host', env('DB_HOST', '127.0.0.1'));
        $app['config']->set('database.connections.mysql.port', env('DB_PORT', '33068'));
        $app['config']->set('database.connections.mysql.database', env('DB_DATABASE', ''));
        $app['config']->set('database.connections.mysql.username', env('DB_USERNAME', 'root'));
        $app['config']->set('database.connections.mysql.password', env('DB_PASSWORD', ''));
        $app['config']->set('database.connections.mysql.modes', [
            'ONLY_FULL_GROUP_BY',
            'STRICT_TRANS_TABLES',
            'NO_ZERO_IN_DATE',
            'NO_ZERO_DATE',
            'ERROR_FOR_DIVISION_BY_ZERO',
            'NO_ENGINE_SUBSTITUTION',
        ]);
    }

    public function useMariaDbConnection($app)
    {
        config(['database.default' => 'mysql']);
        $app['config']->set('database.connections.mysql.host', env('DB_HOST', '127.0.0.1'));
        $app['config']->set('database.connections.mysql.port', env('DB_PORT', '33107'));
        $app['config']->set('database.connections.mysql.database', env('DB_DATABASE', ''));
        $app['config']->set('database.connections.mysql.username', env('DB_USERNAME', 'root'));
        $app['config']->set('database.connections.mysql.password', env('DB_PASSWORD', ''));
        $app['config']->set('database.connections.mysql.modes', [
            'ONLY_FULL_GROUP_BY',
            'STRICT_TRANS_TABLES',
            'NO_ZERO_IN_DATE',
            'NO_ZERO_DATE',
            'ERROR_FOR_DIVISION_BY_ZERO',
            'NO_ENGINE_SUBSTITUTION',
        ]);

    }

    public function usePostgresConnection($app)
    {
        config(['database.default' => 'pgsql']);
        $app['config']->set('database.connections.pgsql.host', env('DB_HOST', '127.0.0.1'));
        $app['config']->set('database.connections.pgsql.port', env('DB_PORT', '54314'));
        $app['config']->set('database.connections.pgsql.database', env('DB_DATABASE', 'forge'));
        $app['config']->set('database.connections.pgsql.username', env('DB_USERNAME', 'postgres'));
        $app['config']->set('database.connections.pgsql.password', env('DB_PASSWORD', 'password'));
        $app['config']->set('database.connections.pgsql.schema', 'public');
        $app['config']->set('database.connections.pgsql.search_path', 'public');
    }

    protected function useSQLiteConnection($app)
    {
        config(['database.default' => 'testbench']);
        config(['database.connections.testbench' => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]]);
    }

    protected function useSqlServerConnection($app)
    {
        config(['database.default' => 'sqlsrv']);
        $app['config']->set('database.connections.sqlsrv.host', env('DB_HOST', '127.0.0.1'));
        $app['config']->set('database.connections.sqlsrv.port', env('DB_PORT', '14330'));
        $app['config']->set('database.connections.sqlsrv.database', env('DB_DATABASE', ''));
        $app['config']->set('database.connections.sqlsrv.username', env('DB_USERNAME', 'SA'));
        $app['config']->set('database.connections.sqlsrv.password', env('DB_PASSWORD', 'MyPass@word'));
        $app['config']->set('database.connections.sqlsrv.odbc_driver',   '{ODBC Driver 18 for SQL Server}');
        $app['config']->set('database.connections.sqlsrv.trust_server_certificate', 'true');
    }
}