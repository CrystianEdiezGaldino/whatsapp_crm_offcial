<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        $indexes = DB::select("
            SELECT DISTINCT
                OBJECT_NAME(i.object_id) AS table_name,
                i.name AS index_name,
                i.is_unique
            FROM sys.indexes i
            INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
            INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
            INNER JOIN sys.types ty ON c.system_type_id = ty.system_type_id
            WHERE ty.name = 'datetime'
              AND OBJECT_SCHEMA_NAME(i.object_id) = 'dbo'
              AND i.name IS NOT NULL
              AND i.is_primary_key = 0
              AND i.is_unique_constraint = 0
        ");

        $savedIndexes = [];

        foreach ($indexes as $index) {
            $columns = DB::select("
                SELECT c.name, ic.is_descending_key
                FROM sys.index_columns ic
                INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
                INNER JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
                WHERE i.name = ? AND OBJECT_NAME(i.object_id) = ?
                ORDER BY ic.key_ordinal
            ", [$index->index_name, $index->table_name]);

            $savedIndexes[] = [
                'table' => $index->table_name,
                'name' => $index->index_name,
                'unique' => (bool) $index->is_unique,
                'columns' => $columns,
            ];

            DB::statement("DROP INDEX [{$index->index_name}] ON [{$index->table_name}]");
        }

        $defaults = DB::select("
            SELECT
                OBJECT_NAME(dc.parent_object_id) AS table_name,
                dc.name AS constraint_name
            FROM sys.default_constraints dc
            INNER JOIN sys.columns c ON dc.parent_object_id = c.object_id AND dc.parent_column_id = c.column_id
            INNER JOIN sys.types ty ON c.system_type_id = ty.system_type_id
            WHERE ty.name = 'datetime'
              AND OBJECT_SCHEMA_NAME(dc.parent_object_id) = 'dbo'
        ");

        foreach ($defaults as $default) {
            DB::statement("ALTER TABLE [{$default->table_name}] DROP CONSTRAINT [{$default->constraint_name}]");
        }

        $columns = DB::select("
            SELECT TABLE_NAME, COLUMN_NAME, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE DATA_TYPE = 'datetime' AND TABLE_SCHEMA = 'dbo'
        ");

        foreach ($columns as $column) {
            $nullable = $column->IS_NULLABLE === 'YES' ? 'NULL' : 'NOT NULL';
            DB::statement(sprintf(
                'ALTER TABLE [%s] ALTER COLUMN [%s] datetime2 %s',
                $column->TABLE_NAME,
                $column->COLUMN_NAME,
                $nullable
            ));
        }

        foreach ($savedIndexes as $index) {
            $columnList = collect($index['columns'])
                ->map(fn ($column) => '['.$column->name.']'.($column->is_descending_key ? ' DESC' : ' ASC'))
                ->implode(', ');

            $unique = $index['unique'] ? 'UNIQUE ' : '';
            DB::statement("CREATE {$unique}INDEX [{$index['name']}] ON [{$index['table']}] ({$columnList})");
        }
    }

    public function down(): void
    {
        //
    }
};
