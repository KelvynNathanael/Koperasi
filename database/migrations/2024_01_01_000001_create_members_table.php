<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code', 20)->unique();
            $table->string('full_name', 150);
            $table->string('phone_number', 20)->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'nonactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};