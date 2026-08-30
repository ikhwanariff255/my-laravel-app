#!/bin/bash

# Configuration (Edit these two lines!)
OLD_USER="root"
OLD_IP="204.168.200.205"

# Paths
OLD_DIR="/opt/lampp/htdocs/assets/uploads/"
NEW_DIR="/var/www/my-laravel-app/storage/app/public"

# Create logos folder if it doesn't exist
mkdir -p $NEW_DIR/logos

echo "--- Downloading Inspection Images ---"
rsync -avzP --files-from=$NEW_DIR/inspections_list.txt ${OLD_USER}@${OLD_IP}:${OLD_DIR} $NEW_DIR/inspections/

echo "--- Downloading Defect Images ---"
rsync -avzP --files-from=$NEW_DIR/defects_list.txt ${OLD_USER}@${OLD_IP}:${OLD_DIR} $NEW_DIR/defects/

echo "--- Downloading Company Logos ---"
rsync -avzP --files-from=$NEW_DIR/logos_list.txt ${OLD_USER}@${OLD_IP}:${OLD_DIR} $NEW_DIR/logos/

echo "--- Fixing Permissions ---"
sudo chown -R www-data:www-data $NEW_DIR/inspections $NEW_DIR/defects $NEW_DIR/logos
sudo chmod -R 775 $NEW_DIR/inspections $NEW_DIR/defects $NEW_DIR/logos

echo "Transfer Complete!"