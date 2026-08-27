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
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        // Menghubungkan invois kepada user/owner
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
        
        $table->string('inv_no')->unique();
        $table->date('date');
        $table->string('cus_name');
        $table->decimal('grand_total', 10, 2)->default(0.00);
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
