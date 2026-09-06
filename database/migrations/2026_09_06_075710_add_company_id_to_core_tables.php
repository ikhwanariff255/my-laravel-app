<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
    });

    Schema::table('inspections', function (Blueprint $table) {
        $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
        $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
    });

    Schema::table('invoices', function (Blueprint $table) {
        $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('core_tables', function (Blueprint $table) {
            //
        });
    }
};
