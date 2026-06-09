<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 80);
            $table->unsignedBigInteger('record_id');
            $table->string('action', 20);   // created, updated, deleted
            $table->jsonb('old_data')->nullable();
            $table->jsonb('new_data')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['table_name', 'record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};