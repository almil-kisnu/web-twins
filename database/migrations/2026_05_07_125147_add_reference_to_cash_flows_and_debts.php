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
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->uuid('reference_id')->nullable()->after('tanggal');
            $table->string('reference_type')->nullable()->after('reference_id');
            if (!Schema::hasColumn('cash_flows', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->uuid('reference_id')->nullable()->after('jatuh_tempo');
            $table->string('reference_type')->nullable()->after('reference_id');
            if (!Schema::hasColumn('debts', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->dropColumn(['reference_id', 'reference_type', 'created_at', 'updated_at']);
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->dropColumn(['reference_id', 'reference_type', 'created_at', 'updated_at']);
        });
    }
};
