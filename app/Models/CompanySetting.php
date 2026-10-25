<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Office hours set by the Owner, falling back to config/attendance.php (.env)
    public static function officeHours(): array
    {
        $settings = static::first();

        return [
            'start' => $settings?->office_start_time ?? config('attendance.late_after'),
            'grace' => (int) ($settings?->office_grace_minutes ?? config('attendance.late_grace_minutes')),
            'end' => $settings?->office_end_time ?? config('attendance.end_time'),
        ];
    }
}
