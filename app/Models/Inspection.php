<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    // Tambah 'user_id' di sini supaya Laravel benarkan ia disimpan
    protected $fillable = [
        'user_id', 
        'title', 
        'clientname', 
        'address', 
        'state', 
        'type', 
        'img', 
        'layout_img'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function defects()
    {
        return $this->hasMany(Defect::class);
    }

    public function staffs()
    {
        return $this->belongsToMany(User::class, 'inspection_user', 'inspection_id', 'user_id');
    }
}