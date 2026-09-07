<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ssm',
        'cidb',
        'tokens_left',
        'package_id',
    ];

    /**
     * Boot method untuk auto-create Admin Syarikat bila company baru dicipta.
     */
    protected static function booted()
    {
        static::created(function ($company) {
            // Jana e-mel secara automatik berdasarkan nama syarikat (boleh diubah nanti)
            $slugName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $company->name));
            $adminEmail = $slugName . '@defexsnap.com';

            // Cipta akaun Admin Syarikat secara automatik
            User::create([
                'name'       => $company->name . ' admin',
                'email'      => $adminEmail,
                'username'   => $slugName,
                'password'   => Hash::make('password123'), // Kata laluan sementara
                'company_id' => $company->id,
                'role'       => 'company_admin',         // Peranan sebagai admin syarikat
            ]);
        });
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}