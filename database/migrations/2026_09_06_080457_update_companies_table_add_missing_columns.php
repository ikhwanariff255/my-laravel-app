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
        if (!Schema::hasColumn('companies', 'name')) {
            $table->string('name')->nullable();
        }
        if (!Schema::hasColumn('companies', 'tokens_left')) {
            $table->integer('tokens_left')->default(0);
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
