# ExpressionGrammar
## Grammar helper for providing expressions with grammar differences by database

Sometimes SQL expressions need to provide different grammar for different databases and for different versions of databases.

This package provides an `ExpressionGrammar` class that will produce the appropriate expression for the database and version in use.

For example, when [Excel Seeder for Laravel](https://github.com/bfinlay/laravel-excel-seeder) tests exception messages, it can resolve the grammar based on the database used: 
```php
        $this->expectExceptionMessage(ExpressionGrammar::make()
            ->sqLite('Integrity constraint violation: 19 FOREIGN KEY constraint failed')
            ->mySql('Syntax error or access violation: 1701 Cannot truncate a table referenced in a foreign key constraint')
            ->sqlServer('Cannot truncate table \'users\' because it is being referenced by a FOREIGN KEY constraint.')
        );
        \DB::table('users')->truncate();
```


As another example, when working with `ST_GeomFromText()` between MySQL 8.0 vs MySQL 5.7 and Postgres, the order of latitude and longitude is different,
and when switching between databases you might want your code base to work the same without changes.  MySQL 8.0 provides an option
for `ST_GeomFromText()` to change the axis order. So while the grammar for Postgres will look like `ST_GeomFromText('POINT(1 2)', 4326)`,
the grammar for MySql 8.0 will look like `ST_GeomFromText('POINT(1 2)', 4326, 'axis-order=long-lat')`.

Creating an `Expression` with an `ExpressionGrammar` to support these three different grammars would look like this:
```php
$expression = ExpressionGrammar::make()
        ->mySql("ST_GeomFromText('POINT(1 2)', 4326)")
        ->mySql("ST_GeomFromText('POINT(1 2)', 4326, 'axis-order=long-lat')", "8.0")
        ->postgres("ST_GeomFromText('POINT(1 2)', 4326)");
```
This will resolve to the following expressions for the specified databases and versions:

| database | version | result |
|----------|---------|--------|
| MySQL    | default | ST_GeomFromText('POINT(1 2)', 4326) |
| MySQL    | 8.0 and higher | ST_GeomFromText('POINT(1 2)', 4326, 'axis-order=long-lat') |
| Postgres | default | ST_GeomFromText('POINT(1 2)', 4326) |

Laravel Expressions do not support bindings by default.  [Expressions for Laravel](https://github.com/Angel-Source-Labs/laravel-expressions) is a package that provides Expressions that support bindings, which allows the ExpressionGrammar helper to return Expressions with bindings that will be evaluated by the query builder.

### Available Methods
The `ExpressionGrammar` class provides a fluent interface for adding grammar expressions and has methods for each built-in Laravel driver as well
as a generic `grammar` method that allows specifying a driver string for other databases.

#### #`ExpressionGrammar::make()`
Creates a new Grammar instance and provides a fluent interface for adding grammar expressions.

#### #`ExpressionGrammar->mySql($string, $version (optional))`
Add an expression for MySQL grammar.

#### #`ExpressionGrammar->postgres($string, $version (optional))`
Add an expression for Postgres grammar.

#### #`ExpressionGrammar->sqLite($string, $version (optional))`
Add an expression for SQLite grammar.

#### #`ExpressionGrammar->sqlServer($string, $version (optional))`
Add an expression for SqlServer grammar.

#### #`ExpressionGrammar->grammar($driver, $string, $version (optional))`

Add an expression for grammar for other database drivers.  `$driver` should match the driver string used by the Laravel query builder driver.
For example `$grammar->postgres("ST_GeomFromText('POINT(1 2)', 4326)")` is equivalent to `$grammar->grammar("pgsql", "ST_GeomFromText('POINT(1 2)', 4326)")`.

The `$version` parameter is optional.  When not specified, the grammar applies as the default.  When specified, the grammar applies to the specified version of the database or greater.

`ExpressionGrammar` will throw a `GrammarNotDefinedForDatabaseException` if the Query Builder attempts to resolve an Expression for a Grammar that has not been defined for that database driver.

## License
ExpresionGrammar for Laravel is open-sourced software licensed under the MIT license.
