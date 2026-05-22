<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('macro_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('macro_id');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedInteger('file_size');
            $table->enum('file_type', ['audio', 'video', 'document', 'image']);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->foreign('macro_id')->references('id')->on('macros')->cascadeOnDelete();
            $table->index('macro_id');
            $table->index('file_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('macro_files');
    }
};
