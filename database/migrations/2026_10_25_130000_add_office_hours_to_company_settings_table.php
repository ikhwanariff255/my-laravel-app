<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            // Null = fall back to config/attendance.php (.env)
            $table->string('office_start_time', 5)->nullable();
            $table->unsignedSmallInteger('office_grace_minutes')->nullable();
            $table->string('office_end_time', 5)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['office_start_time', 'office_grace_minutes', 'office_end_time']);
        });
    }
};
