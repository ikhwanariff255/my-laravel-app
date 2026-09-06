<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // <-- Pastikan baris ni ada
use App\Models\Company;
use App\Models\User;
use App\Models\Inspection;
use App\Models\Invoice;

class LegacyDataMigrationSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            // Cipta satu syarikat utama untuk menampung data lama
            $company = Company::create([
                'name' => 'Legacy DefexSnap Company',
                'tokens_left' => 0,
            ]);

            // Kemas kini semua rekod lama yang tidak mempunyai syarikat
            User::whereNull('company_id')->update(['company_id' => $company->id]);
            Inspection::whereNull('company_id')->update(['company_id' => $company->id]);
            
            // Periksa jika jadual Invoices wujud sebelum kemas kini
            if (Schema::hasTable('invoices')) {
                Invoice::whereNull('company_id')->update(['company_id' => $company->id]);
            }
        });
    }
}