<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Conversations - campos operacionais
        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                if (!Schema::hasColumn('conversations', 'owner_id')) {
                    $table->unsignedBigInteger('owner_id')->nullable()->after('claimed_by');
                    $table->foreign('owner_id')->references('id')->on('users');
                }

                if (!Schema::hasColumn('conversations', 'assigned_by')) {
                    $table->unsignedBigInteger('assigned_by')->nullable()->after('owner_id');
                    $table->foreign('assigned_by')->references('id')->on('users');
                }

                if (!Schema::hasColumn('conversations', 'assigned_at')) {
                    $table->timestamp('assigned_at')->nullable()->after('assigned_by');
                }

                if (!Schema::hasColumn('conversations', 'last_interaction_at')) {
                    $table->timestamp('last_interaction_at')->nullable()->after('assigned_at');
                }

                if (!Schema::hasColumn('conversations', 'transfer_count')) {
                    $table->integer('transfer_count')->default(0)->after('last_interaction_at');
                }

                if (!Schema::hasColumn('conversations', 'priority_level')) {
                    $table->enum('priority_level', ['low', 'normal', 'high', 'urgent', 'vip'])->default('normal')->after('transfer_count');
                }

                if (!Schema::hasColumn('conversations', 'sla_first_response_expires_at')) {
                    $table->timestamp('sla_first_response_expires_at')->nullable()->after('priority_level');
                    $table->timestamp('sla_resolution_expires_at')->nullable()->after('sla_first_response_expires_at');
                    $table->boolean('sla_first_response_breached')->default(false)->after('sla_resolution_expires_at');
                    $table->boolean('sla_resolution_breached')->default(false)->after('sla_first_response_breached');
                }

                if (!Schema::hasColumn('conversations', 'resolution_category')) {
                    $table->enum('resolution_category', ['solved', 'customer_request', 'transferred', 'abandoned', 'no_answer'])->nullable()->after('sla_resolution_breached');
                }

                if (!Schema::hasColumn('conversations', 'entered_queue_at')) {
                    $table->timestamp('entered_queue_at')->nullable()->after('resolution_category');
                    $table->integer('queue_position')->default(0)->after('entered_queue_at');
                    $table->boolean('is_in_queue')->default(false)->after('queue_position');
                }
            });
        }

        // Sectors - SLA e horário comercial
        if (Schema::hasTable('sectors')) {
            Schema::table('sectors', function (Blueprint $table) {
                if (!Schema::hasColumn('sectors', 'sla_first_response_minutes')) {
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

        // Tags
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('color')->default('#666666');
                $table->enum('category', ['priority', 'status', 'outcome', 'custom'])->default('custom');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['category', 'is_active']);
            });
        }

        // Conversation Tags
        if (!Schema::hasTable('conversation_tags')) {
            Schema::create('conversation_tags', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->unsignedBigInteger('tag_id');
                $table->timestamps();

                $table->unique(['conversation_id', 'tag_id']);
                $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
                $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            });
        }

        // Complaints (QA)
        if (!Schema::hasTable('complaints')) {
            Schema::create('complaints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->unsignedBigInteger('responsible_user_id');
                $table->unsignedBigInteger('reviewed_by')->nullable();

                $table->integer('rating')->default(3);
                $table->text('customer_note')->nullable();
                $table->enum('severity', ['low', 'medium', 'high'])->default('medium');
                $table->enum('status', ['open', 'reviewing', 'resolved', 'dismissed'])->default('open');
                $table->text('review_notes')->nullable();
                $table->enum('action_taken', ['coaching', 'retraining', 'suspension', 'none'])->nullable();

                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
                $table->foreign('responsible_user_id')->references('id')->on('users');
                $table->foreign('reviewed_by')->references('id')->on('users');
                $table->index('status');
                $table->index('severity');
            });
        }

        // Conversation Transfers
        if (!Schema::hasTable('conversation_transfers')) {
            Schema::create('conversation_transfers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->unsignedBigInteger('from_user_id');
                $table->unsignedBigInteger('to_user_id');
                $table->unsignedBigInteger('requested_by');
                $table->unsignedBigInteger('approved_by')->nullable();

                $table->unsignedBigInteger('from_sector_id')->nullable();
                $table->unsignedBigInteger('to_sector_id')->nullable();

                $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
                $table->text('reason')->nullable();
                $table->text('rejection_reason')->nullable();

                $table->timestamp('requested_at')->useCurrent();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
                $table->foreign('from_user_id')->references('id')->on('users');
                $table->foreign('to_user_id')->references('id')->on('users');
                $table->foreign('requested_by')->references('id')->on('users');
                $table->foreign('approved_by')->references('id')->on('users');

                if (Schema::hasTable('sectors')) {
                    $table->foreign('from_sector_id')->references('id')->on('sectors');
                    $table->foreign('to_sector_id')->references('id')->on('sectors');
                }

                $table->index('status');
                $table->index(['conversation_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_transfers');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('conversation_tags');
        Schema::dropIfExists('tags');

        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropForeignKeyIfExists('conversations_owner_id_foreign');
                $table->dropForeignKeyIfExists('conversations_assigned_by_foreign');
                $table->dropColumnIfExists([
                    'owner_id', 'assigned_by', 'assigned_at', 'last_interaction_at', 'transfer_count',
                    'priority_level', 'sla_first_response_expires_at', 'sla_resolution_expires_at',
                    'sla_first_response_breached', 'sla_resolution_breached', 'resolution_category',
                    'entered_queue_at', 'queue_position', 'is_in_queue'
                ]);
            });
        }

        if (Schema::hasTable('sectors')) {
            Schema::table('sectors', function (Blueprint $table) {
                $table->dropForeignKeyIfExists('sectors_overflow_sector_id_foreign');
                $table->dropColumnIfExists([
                    'sla_first_response_minutes', 'sla_resolution_hours', 'working_hours_start', 'working_hours_end',
                    'working_days', 'overflow_sector_id', 'priority_rules', 'auto_assign_mode'
                ]);
            });
        }
    }
};
