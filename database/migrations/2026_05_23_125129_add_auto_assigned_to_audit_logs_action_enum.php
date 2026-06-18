<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $values = ['created', 'updated', 'deleted', 'claimed', 'released', 'assigned', 'used', 'auto_assigned'];

    public function up(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            $this->replaceSqlServerCheckConstraint($this->values);
            return;
        }

        DB::statement("ALTER TABLE audit_logs MODIFY action ENUM('".implode("', '", $this->values)."')");
    }

    public function down(): void
    {
        $old = array_diff($this->values, ['auto_assigned']);

        if (DB::getDriverName() === 'sqlsrv') {
            $this->replaceSqlServerCheckConstraint(array_values($old));
            return;
        }

        DB::statement("ALTER TABLE audit_logs MODIFY action ENUM('".implode("', '", $old)."')");
    }

    private function replaceSqlServerCheckConstraint(array $values): void
    {
        $constraints = DB::select("
            SELECT cc.name
            FROM sys.check_constraints cc
            INNER JOIN sys.columns c ON cc.parent_object_id = c.object_id
            WHERE c.object_id = OBJECT_ID('audit_logs') AND c.name = 'action'
        ");

        foreach ($constraints as $constraint) {
            DB::statement("ALTER TABLE audit_logs DROP CONSTRAINT [{$constraint->name}]");
        }

        $list = implode("', '", $values);
        DB::statement("ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_action_check CHECK (action IN ('{$list}'))");
    }
};
