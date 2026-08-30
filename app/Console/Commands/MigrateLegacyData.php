<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MigrateLegacyData extends Command
{
    protected $signature = 'db:migrate-legacy';
    protected $description = 'Migrate data from miya_db to my_database with strict formatting';

    public function handle()
    {
        $this->info('Starting data migration...');

        $this->migrateUsers();
        $this->migrateInspections();
        $this->migrateDefects();
        $this->migrateInvoices();
        $this->migrateInvoiceDetails();

        $this->info('Migration completed successfully!');
    }

    private function migrateUsers()
    {
        $this->info('Migrating users...');
        $oldUsers = DB::connection('mysql_legacy')->table('users')->get();

        foreach ($oldUsers as $old) {
            // 1. Map old roles to valid new ENUM values safely
            $role = strtolower(trim($old->role));
            
            switch ($role) {
                case 'admin':
                case 'owner':
                    $newRole = 'owner';
                    break;
                case 'inspector':
                case 'staff':
                    $newRole = 'staff';
                    break;
                default:
                    $newRole = 'staff'; // Fallback for any unknown roles
                    break;
            }

            // 2. Use updateOrInsert so we can safely re-run the script after a crash
            DB::table('users')->updateOrInsert(
                ['id' => $old->id], // Find by ID
                [
                    'name' => $old->fullname,
                    'username' => $old->username,
                    'email' => strtolower(str_replace(' ', '', $old->username)) . '@placeholder.com', 
                    'email_verified_at' => null,
                    'password' => Hash::make($old->password),
                    'role' => $newRole,
                    'remember_token' => null,
                    'created_at' => $old->created_at ?? now(),
                    'updated_at' => now(),
                ]
            );

            // Migrate Company Settings safely
            if (!empty($old->company_name)) {
                // Check if company settings for this user's data already exists
                $companyExists = DB::table('company_settings')
                    ->where('company_name', $old->company_name)
                    ->exists();

                if (!$companyExists) {
                    DB::table('company_settings')->insert([
                        'company_name' => $old->company_name,
                        'ssm' => $old->company_ssm,
                        'cidb' => $old->company_cidb,
                        'logo_path' => $old->company_logo,
                        'created_at' => $old->created_at ?? now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function migrateInspections()
    {
        $this->info('Migrating inspections...');
        $oldInspections = DB::connection('mysql_legacy')->table('inspection')->get();

        foreach ($oldInspections as $old) {
            // Format addresses to a single string safely
            $fullAddress = trim(implode(', ', array_filter([$old->address, $old->city, $old->posscode])));

            DB::table('inspections')->updateOrInsert(
                ['id' => $old->id], // Find by ID to prevent duplicate crashes
                [
                    // If user_id is null or 0 in the old DB, assign it to user 1 (Admin)
                    'user_id' => $old->user_id ?: 1, 
                    'title' => $old->title ?? 'Untitled Inspection',
                    'clientname' => $old->client_name ?? 'Unknown Client',
                    'cus_no' => null, 
                    'cus_email' => null, 
                    'inspection_date' => $old->created_at ? Carbon::parse($old->created_at)->format('Y-m-d') : null,
                    'address' => $fullAddress,
                    'state' => $old->state ?? 'Unknown',
                    'type' => $old->type ?? 'General',
                    'img' => $old->image ? 'inspections/' . $old->image : null,
                    'layout_img' => $old->layout ? 'inspections/' . $old->layout : null,
                    'created_at' => $old->created_at ?? now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function migrateDefects()
    {
        $this->info('Migrating defects (items)...');
        $oldItems = DB::connection('mysql_legacy')->table('item')->get();

        foreach ($oldItems as $old) {
            $imageJson = null;
            
            if (!empty($old->image)) {
                // Split the string by the pipe character '|'
                $imagesArray = explode('|', $old->image);
                
                // Add the 'defects/' prefix to each individual image
                $formattedImages = array_map(function ($img) {
                    return 'defects/' . trim($img);
                }, $imagesArray);
                
                // Encode the cleanly formatted array into JSON
                $imageJson = json_encode($formattedImages);
            }

            DB::table('defects')->updateOrInsert(
                ['id' => $old->id],
                [
                    'inspection_id' => $old->inspection_id ?: 1, 
                    'user_id' => $old->user_id ?: 1,
                    'location' => $old->location ?? 'Unknown',
                    'category' => $old->category ?? 'Unknown',
                    'type' => $old->type ?? 'Unknown',
                    'defect' => $old->defect ?? 'Unknown',
                    'desc' => $old->description,
                    'mark_x' => empty($old->marking_x) ? 0 : (float) $old->marking_x,
                    'mark_y' => empty($old->marking_y) ? 0 : (float) $old->marking_y,
                    'img' => $imageJson, 
                    'created_at' => now(), 
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function migrateInvoices()
    {
        $this->info('Migrating invoices...');
        $oldInvoices = DB::connection('mysql_legacy')->table('invoice')->get();

        foreach ($oldInvoices as $old) {
            DB::table('invoices')->updateOrInsert(
                ['id' => $old->id],
                [
                    'user_id' => $old->user_id ?: 1,
                    'inv_no' => $old->invoice_no ?? 'INV-UNKNOWN', 
                    'date' => $old->invoice_date ?? now()->format('Y-m-d'),
                    'cus_name' => $old->customer_name ?? 'Unknown',
                    'cus_address' => $old->customer_address,
                    'grand_total' => $old->grand_total ?? 0,
                    'created_at' => $old->created_at ?? now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function migrateInvoiceDetails()
    {
        $this->info('Migrating invoice details...');
        $oldInvoiceItems = DB::connection('mysql_legacy')->table('invoice_items')->get();

        foreach ($oldInvoiceItems as $old) {
            DB::table('invoice_details')->updateOrInsert(
                ['id' => $old->id],
                [
                    'invoice_id' => $old->invoice_id ?: 1,
                    'desc' => $old->description ?? 'Item description missing', 
                    'price' => $old->price ?? 0,
                    'created_at' => now(), 
                    'updated_at' => now(),
                ]
            );
        }
    }
}