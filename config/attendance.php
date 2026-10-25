<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Office Location
    |--------------------------------------------------------------------------
    |
    | Coordinates of the office and the radius (in meters) within which staff
    | are allowed to clock in. Read via config() so values survive
    | `php artisan config:cache`.
    |
    | Set the real office location in .env (OFFICE_LATITUDE / OFFICE_LONGITUDE),
    | never in this file. The defaults are a placeholder (Kuala Lumpur city
    | centre).
    |
    */

    'office_latitude' => (float) env('OFFICE_LATITUDE', 3.139003),

    'office_longitude' => (float) env('OFFICE_LONGITUDE', 101.686855),

    'radius_meters' => (int) env('OFFICE_RADIUS_METERS', 100),

    /*
    |--------------------------------------------------------------------------
    | Office Hours
    |--------------------------------------------------------------------------
    |
    | Times are HH:MM in the app timezone. Clock-ins after `late_after` plus
    | `late_grace_minutes` are marked late (e.g. 08:00 + 5 = late after
    | 08:05); clock-outs before `end_time` are flagged as early.
    | `work_days` uses ISO day numbers (1 = Monday ... 7 = Sunday).
    |
    | These are defaults only: the Owner can override start time, grace and
    | end time from the Attendance Dashboard (stored in company_settings).
    |
    */

    'late_after' => env('OFFICE_LATE_AFTER', '08:00'),

    'late_grace_minutes' => (int) env('OFFICE_LATE_GRACE_MINUTES', 0),

    'end_time' => env('OFFICE_END_TIME', '17:00'),

    'work_days' => [1, 2, 3, 4, 5],

];
