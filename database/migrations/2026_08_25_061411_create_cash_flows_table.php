<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();
            
            // Jenis transaksi: 'in' (Duit Masuk) atau 'out' (Duit Keluar)
            $table->enum('type', ['in', 'out']); 
            
            // Kategori untuk rujukan mudah (cth: 'Invoice', 'Part-Time', 'Petrol', 'Manual')
            $table->string('category')->nullable(); 
            
            $table->decimal('amount', 10, 2);
            $table->string('description'); // Keterangan transaksi
            $table->date('date'); // Tarikh transaksi
            
            // Nombor rujukan (Boleh jadi nombor Invoice atau nombor Resit Manual)
            $table->string('reference_no')->nullable(); 
            
            // Relasi ke table invoices (nullable sebab ada transaksi manual yang tiada invois)
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('cascade');
            
            // Relasi ke table users (nullable, digunakan jika transaksi ini adalah bayaran gaji kepada staf)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_flows');
    }
};