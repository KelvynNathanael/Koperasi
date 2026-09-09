<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE loans DROP CONSTRAINT loans_installment_frequency_check');
        DB::statement("ALTER TABLE loans ADD CONSTRAINT loans_installment_frequency_check CHECK (installment_frequency IN ('daily', 'weekly', 'monthly', 'tempo'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE loans DROP CONSTRAINT loans_installment_frequency_check');
        DB::statement("ALTER TABLE loans ADD CONSTRAINT loans_installment_frequency_check CHECK (installment_frequency IN ('daily', 'weekly', 'monthly'))");
    }
};