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
        // Tukar 'cashflows' kepada 'cash_flows'
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->string('receipt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tukar 'cashflows' kepada 'cash_flows'
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->dropColumn('receipt');
        });
    }
};