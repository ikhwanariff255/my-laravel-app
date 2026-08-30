<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected $appends = ['image_urls'];

    public function getImageUrlsAttribute()
    {
        if (empty($this->img)) return [];

        // Decode the JSON string into a PHP array (if it isn't cast to an array already)
        $paths = is_array($this->img) ? $this->img : json_decode($this->img, true);

        if (!is_array($paths)) return [];

        // Generate a presigned URL for every image in the array
        return array_map(function ($path) {
            return Storage::disk('s3')->temporaryUrl(
                $path, now()->addMinutes(60)
            );
        }, $paths);
    }
}