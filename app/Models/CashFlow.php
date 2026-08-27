<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashFlow extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Hubungan dengan Invois (Cash flow ini milik invois mana?)
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Hubungan dengan Staff/User (Jika Cash Out ini untuk bayaran part-time)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}