<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Inspection extends Model
{
    // Tambah 'user_id' di sini supaya Laravel benarkan ia disimpan
    protected $fillable = [
        'company_id',
        'template_id',
        'user_id',
        'title',
        'clientname',
        'address',
        'state',
        'type',
        'img',
        'layout_img',
        'cus_no',
        'cus_email',
        'inspection_date',
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

    // Generate secure URL for the main image
    public function getImageUrlAttribute()
    {
        if (!$this->img) return null;
        
        return Storage::disk('s3')->temporaryUrl(
            $this->img, now()->addMinutes(60)
        );
    }

    // Generate secure URL for the layout image
    public function getLayoutUrlAttribute()
    {
        if (!$this->layout_img) return null;
        
        return Storage::disk('s3')->temporaryUrl(
            $this->layout_img, now()->addMinutes(60)
        );
    }
  public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    
}
