<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clock_in',
        'clock_out',
        'early_out',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'distance_meters',
        'status',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'early_out' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function isLateAt(Carbon $time): bool
    {
        return $time->gt(static::lateCutoff($time));
    }

    // Start time plus grace period, on the same day as $time
    public static function lateCutoff(?Carbon $time = null): Carbon
    {
        $hours = CompanySetting::officeHours();

        return ($time ?? now())->copy()
            ->setTimeFromTimeString($hours['start'])
            ->addMinutes($hours['grace']);
    }

    public static function isEarlyOutAt(Carbon $time): bool
    {
        return $time->lt($time->copy()->setTimeFromTimeString(CompanySetting::officeHours()['end']));
    }
}
