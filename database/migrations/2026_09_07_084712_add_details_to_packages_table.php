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
    Schema::table('packages', function (Blueprint $table) {
        $table->string('name')->after('id');
        $table->decimal('price', 8, 2)->default(0.00)->after('name');
        $table->integer('max_reports')->default(0)->after('price');
        $table->integer('max_users')->default(1)->after('max_reports');
    });
}

public function down()
{
    Schema::table('packages', function (Blueprint $table) {
        $table->dropColumn(['name', 'price', 'max_reports', 'max_users']);
    });
}
};
