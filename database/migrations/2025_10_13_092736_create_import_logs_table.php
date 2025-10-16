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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('import_id', 100)->unique()->index();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('merchant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('filename', 255);
            $table->string('file_path', 500)->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'partial'])
                  ->default('pending')->index();
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('merchant_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};