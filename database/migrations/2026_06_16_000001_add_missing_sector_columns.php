<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sectors')) {
            return;
        }

        Schema::table('sectors', function (Blueprint $table) {
            if (! Schema::hasColumn('sectors', 'sla_first_response_minutes')) {
                $table->integer('sla_first_response_minutes')->default(15)->after('is_active');
                $table->integer('sla_resolution_hours')->default(24)->after('sla_first_response_minutes');
                $table->time('working_hours_start')->default('08:00')->after('sla_resolution_hours');
                $table->time('working_hours_end')->default('17:00')->after('working_hours_start');
                $table->string('working_days')->default('1,2,3,4,5')->after('working_hours_end');
                $table->unsignedBigInteger('overflow_sector_id')->nullable()->after('working_days');
                $table->json('priority_rules')->nullable()->after('overflow_sector_id');
                $table->enum('auto_assign_mode', ['manual', 'auto', 'queue'])->default('manual')->after('priority_rules');
                $table->foreign('overflow_sector_id')->references('id')->on('sectors');
            }
        });
    }

    public function down(): void
    {
        //
    }
};
