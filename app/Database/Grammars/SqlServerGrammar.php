<?php

namespace App\Database\Grammars;

use Illuminate\Database\Schema\Grammars\SqlServerGrammar as BaseSqlServerGrammar;
use Illuminate\Support\Fluent;

class SqlServerGrammar extends BaseSqlServerGrammar
{
    public function compileTables()
    {
        return "select TABLE_NAME as name, TABLE_SCHEMA as [schema], 0 as size "
            ."from INFORMATION_SCHEMA.TABLES "
            ."where TABLE_TYPE = 'BASE TABLE' "
            .'order by TABLE_NAME';
    }

    public function compileTableExists()
    {
        return "select 1 from INFORMATION_SCHEMA.TABLES "
            ."where TABLE_NAME = ? and TABLE_TYPE = 'BASE TABLE'";
    }

    protected function typeTimestamp(Fluent $column)
    {
        return $column->precision ? "datetime2($column->precision)" : 'datetime2';
    }
}
