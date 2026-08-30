<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportImagePaths extends Command
{
    protected $signature = 'db:export-images';
    protected $description = 'Generate lists and a bash script to rsync images directly to correct folders';

    public function handle()
    {
        $this->info('Scanning legacy database for images...');
        
        $storagePath = storage_path('app/public');
        
        // 1. Get Inspections
        $inspections = DB::connection('mysql_legacy')->table('inspection')->get(['image', 'layout']);
        $inspectionsList = [];
        foreach ($inspections as $row) {
            if (!empty($row->image)) $inspectionsList[] = trim($row->image);
            if (!empty($row->layout)) $inspectionsList[] = trim($row->layout);
        }

        // 2. Get Defects (Items)
        $defectsList = [];
        $items = DB::connection('mysql_legacy')->table('item')->get(['image']);
        foreach ($items as $row) {
            if (!empty($row->image)) {
                $parts = explode('|', $row->image);
                foreach ($parts as $part) {
                    if (!empty($part)) $defectsList[] = trim($part);
                }
            }
        }

        // 3. Get Company Logos
        $logosList = [];
        $users = DB::connection('mysql_legacy')->table('users')->whereNotNull('company_logo')->get(['company_logo']);
        foreach ($users as $row) {
            if (!empty($row->company_logo)) $logosList[] = trim($row->company_logo);
        }

        // Clean duplicates
        $inspectionsList = array_values(array_unique($inspectionsList));
        $defectsList = array_values(array_unique($defectsList));
        $logosList = array_values(array_unique($logosList));

        // Create the text files
        file_put_contents("$storagePath/inspections_list.txt", implode("\n", $inspectionsList));
        file_put_contents("$storagePath/defects_list.txt", implode("\n", $defectsList));
        file_put_contents("$storagePath/logos_list.txt", implode("\n", $logosList));

        $this->info(count($inspectionsList) . ' inspections, ' . count($defectsList) . ' defects, and ' . count($logosList) . ' logos found.');

        // 4. Generate the Bash Script
        $bashScript = <<<BASH
#!/bin/bash

# Configuration (Edit these two lines!)
OLD_USER="root"
OLD_IP="YOUR_OLD_SERVER_IP"

# Paths
OLD_DIR="/opt/lampp/htdocs/assets/uploads/"
NEW_DIR="/var/www/my-laravel-app/storage/app/public"

# Create logos folder if it doesn't exist
mkdir -p \$NEW_DIR/logos

echo "--- Downloading Inspection Images ---"
rsync -avzP --files-from=\$NEW_DIR/inspections_list.txt \${OLD_USER}@\${OLD_IP}:\${OLD_DIR} \$NEW_DIR/inspections/

echo "--- Downloading Defect Images ---"
rsync -avzP --files-from=\$NEW_DIR/defects_list.txt \${OLD_USER}@\${OLD_IP}:\${OLD_DIR} \$NEW_DIR/defects/

echo "--- Downloading Company Logos ---"
rsync -avzP --files-from=\$NEW_DIR/logos_list.txt \${OLD_USER}@\${OLD_IP}:\${OLD_DIR} \$NEW_DIR/logos/

echo "--- Fixing Permissions ---"
sudo chown -R www-data:www-data \$NEW_DIR/inspections \$NEW_DIR/defects \$NEW_DIR/logos
sudo chmod -R 775 \$NEW_DIR/inspections \$NEW_DIR/defects \$NEW_DIR/logos

echo "Transfer Complete!"
BASH;

        // Save bash script and make it executable
        file_put_contents(base_path('transfer_images.sh'), $bashScript);
        chmod(base_path('transfer_images.sh'), 0755);

        $this->info('Success! Run the transfer script using: ./transfer_images.sh');
    }
}