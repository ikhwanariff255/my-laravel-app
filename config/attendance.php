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
    */

    'office_latitude' => (float) env('OFFICE_LATITUDE', 3.139003),

    'office_longitude' => (float) env('OFFICE_LONGITUDE', 101.686855),

    'radius_meters' => (int) env('OFFICE_RADIUS_METERS', 100),

    // Clock-ins after this time (HH:MM, app timezone) are marked as late
    'late_after' => env('OFFICE_LATE_AFTER', '08:30'),

];
