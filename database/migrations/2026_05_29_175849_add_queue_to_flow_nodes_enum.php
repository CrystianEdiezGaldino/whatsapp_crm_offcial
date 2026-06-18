<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $upValues = ['message', 'menu', 'action', 'queue'];
    private array $downValues = ['message', 'menu', 'action'];

    public function up(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            $this->replaceSqlServerCheckConstraint($this->upValues);
            return;
        }

        DB::statement("ALTER TABLE flow_nodes MODIFY node_type ENUM('".implode("', '", $this->upValues)."') DEFAULT 'message'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            $this->replaceSqlServerCheckConstraint($this->downValues);
            return;
        }

        DB::statement("ALTER TABLE flow_nodes MODIFY node_type ENUM('".implode("', '", $this->downValues)."') DEFAULT 'message'");
    }

    private function replaceSqlServerCheckConstraint(array $values): void
    {
        if (! DB::getSchemaBuilder()->hasTable('flow_nodes')) {
            return;
        }

        $constraints = DB::select("
            SELECT cc.name
            FROM sys.check_constraints cc
            INNER JOIN sys.columns c ON cc.parent_object_id = c.object_id
            WHERE c.object_id = OBJECT_ID('flow_nodes') AND c.name = 'node_type'
        ");

        foreach ($constraints as $constraint) {
            DB::statement("ALTER TABLE flow_nodes DROP CONSTRAINT [{$constraint->name}]");
        }

        $list = implode("', '", $values);
        DB::statement("ALTER TABLE flow_nodes ADD CONSTRAINT flow_nodes_node_type_check CHECK (node_type IN ('{$list}'))");
    }
};
