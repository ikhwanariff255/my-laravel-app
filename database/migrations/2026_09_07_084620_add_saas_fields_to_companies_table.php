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
    Schema::table('companies', function (Blueprint $table) {
        $table->string('ssm')->nullable()->after('name');
        $table->string('cidb')->nullable()->after('ssm');
        $table->unsignedBigInteger('package_id')->nullable()->after('tokens_left');
    });
}

public function down()
{
    Schema::table('companies', function (Blueprint $table) {
        $table->dropColumn(['ssm', 'cidb', 'package_id']);
    });
}
};
