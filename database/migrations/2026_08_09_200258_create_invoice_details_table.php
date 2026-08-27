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
    Schema::create('invoice_details', function (Blueprint $table) {
        $table->id();
        // Foreign key ke table invoices (dalam ERD tulis inv_id, tapi standard Laravel guna invoice_id)
        $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
        
        $table->string('desc');
        $table->decimal('price', 10, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
