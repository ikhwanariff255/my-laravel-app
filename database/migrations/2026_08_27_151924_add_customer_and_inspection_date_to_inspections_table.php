<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->string('cus_no')->nullable()->after('clientname');
            $table->string('cus_email')->nullable()->after('cus_no');
            $table->date('inspection_date')->nullable()->after('cus_email');
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn(['cus_no', 'cus_email', 'inspection_date']);
        });
    }
};
