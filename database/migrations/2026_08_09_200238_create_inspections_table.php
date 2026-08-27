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
    Schema::create('inspections', function (Blueprint $table) {
        $table->id();
        // Foreign key ke table users
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        $table->string('title');
        $table->string('clientname');
        $table->text('address');
        $table->string('state');
        $table->string('type');
        $table->string('img')->nullable();
        $table->string('layout_img')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
