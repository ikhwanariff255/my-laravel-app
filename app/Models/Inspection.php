<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Inspection extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'clientname',
        'cus_no',
        'cus_email',
        'inspection_date',
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

    protected $appends = ['image_url', 'layout_url'];

    // Generate local public URL for the main image
    public function getImageUrlAttribute()
    {
        if (!$this->img) return null;
        
        return Storage::disk('public')->url($this->img);
    }

    // Generate local public URL for the layout image
    public function getLayoutUrlAttribute()
    {
        if (!$this->layout_img) return null;
        
        return Storage::disk('public')->url($this->layout_img);
    }
}