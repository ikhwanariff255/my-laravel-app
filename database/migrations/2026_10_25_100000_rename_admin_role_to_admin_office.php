<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The 'admin' role has been replaced by 'admin_office'
        DB::table('users')->where('role', 'admin')->update(['role' => 'admin_office']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'admin_office')->update(['role' => 'admin']);
    }
};
