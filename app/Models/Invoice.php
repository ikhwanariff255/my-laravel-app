<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function cashFlows()
    {
        return $this->hasMany(CashFlow::class);
    }

    // Hubungan ke items/details (Ini mesti dah ada, cuma saya ingatkan)
    public function details()
    {
        return $this->hasMany(InvoiceDetail::class); // Sesuaikan nama kelas jika berbeza (cth: InvoiceItem)
    }
}
