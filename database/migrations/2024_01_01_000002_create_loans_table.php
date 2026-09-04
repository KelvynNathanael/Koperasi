<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_percent', 5, 2)->default(0);
            $table->decimal('total_due', 15, 2);
            $table->decimal('remaining_balance', 15, 2);
            $table->unsignedSmallInteger('duration_months');
            $table->enum('installment_frequency', ['daily', 'weekly', 'monthly'])->default('monthly');
            $table->date('start_date');
            $table->enum('status', ['active', 'paid', 'overdue', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};