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
    Schema::create('defects', function (Blueprint $table) {
        $table->id();
        // Foreign keys
        $table->foreignId('inspection_id')->constrained('inspections')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        $table->string('location');
        $table->string('category');
        $table->string('type');
        $table->string('defect');
        $table->text('desc')->nullable();
        // decimal sesuai untuk koordinat mark_x dan mark_y
        $table->decimal('mark_x', 8, 2)->nullable();
        $table->decimal('mark_y', 8, 2)->nullable();
        $table->string('img')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defects');
    }
};
