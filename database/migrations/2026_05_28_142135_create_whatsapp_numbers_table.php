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
        Schema::create('whatsapp_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();
            $table->string('display_name');
            $table->text('access_token');
            $table->string('business_account_id')->nullable();
            $table->string('phone_number_id')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('is_active');
        });

        // Add active_whatsapp_number_id to settings if not exists
        if (Schema::hasTable('settings') && !Schema::hasColumn('settings', 'active_whatsapp_number_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unsignedBigInteger('active_whatsapp_number_id')->nullable();
                $table->foreign('active_whatsapp_number_id')->references('id')->on('whatsapp_numbers');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_numbers');
    }
};
