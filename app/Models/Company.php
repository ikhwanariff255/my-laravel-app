<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    // Membenarkan seeder memasukkan data
    protected $guarded = []; 

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }
}