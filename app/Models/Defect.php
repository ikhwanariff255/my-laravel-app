<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Defect extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Tukar format data untuk koordinat dan gambar
    protected $casts = [
        'img' => 'array',
        'mark_x' => 'decimal:2',
        'mark_y' => 'decimal:2',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Tambah kod ini di dalam class Inspection
    public function defects()
    {
        return $this->hasMany(Defect::class);
    }
}