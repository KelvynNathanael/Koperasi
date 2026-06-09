<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->enum('flow_type', ['in', 'out']);
            $table->enum('category', ['contribution', 'loan_disbursement', 'repayment', 'expense', 'adjustment']);
            $table->decimal('amount', 15, 2);
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_flows');
    }
};