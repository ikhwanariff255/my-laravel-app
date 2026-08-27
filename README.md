# DefectGuru - Professional Defect Inspection Management System

DefectGuru is a comprehensive Laravel-based web application designed to streamline property inspection processes, defect documentation, and professional report generation.

## 🎯 Features

- **Project Management** - Create and manage multiple inspection projects
- **Defect Tracking** - Document defects with locations, categories, and evidence photos
- **Visual Mapping** - Mark defect locations on floor plans with automated red spot indicators
- **Professional Reports** - Generate high-quality PDF reports with multiple templates
- **Image Processing** - Integrated image cropping, annotation, and evidence capture
- **User Management** - Role-based access control for staff and administrators
- **Financial Management** - Invoice generation and cash flow tracking
- **Responsive Design** - Works seamlessly on desktop, tablet, and mobile devices

## 📋 System Requirements

- PHP 8.1 or higher
- Composer
- MySQL 8.0 or higher
- Node.js & npm
- GD Library (for image processing)

## ⚙️ Installation & Setup

### Step 1: Install Dependencies
```bash
cd defectguru
composer install
npm install
```

### Step 2: Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

### Step 3: Database Setup
```bash
# Update .env with your database credentials
php artisan migrate
php artisan storage:link
```

### Step 4: Build Assets
```bash
npm run build
```

### Step 5: Start Application
```bash
php artisan serve
```

Visit: http://localhost:8000

## 🆕 Recent Updates (v2.0)

### ✅ PDF Template 2 Fixed
- **Location-Specific Defect Mapping**: Each location page now shows only relevant defects marked with red spots
- **Professional Appearance**: Clean, organized report layout
- **Improved Performance**: Faster PDF generation

### 🎨 UI Enhancements
- Professional blue gradient navigation
- Enhanced button and card styling
- Improved table layouts
- Better color scheme

## 📖 Usage

### Create Inspection
1. Inspections → Create New
2. Enter project details
3. Upload floor plan
4. Assign staff
5. Save

### Add Defects
1. Go to inspection
2. Click Add Defect
3. Mark location on floor plan
4. Add photos and details
5. Save

### Generate Reports
1. Open inspection
2. Click Generate Report
3. Choose Template 2 (recommended)
4. Download PDF

## 🔧 Troubleshooting

**Missing Extensions?**
```bash
sudo apt-get install php-gd
```

**Permission Issues?**
```bash
chmod -R 775 storage/ bootstrap/cache/
```

**Database Error?**
```bash
mysql -u root -p -e "CREATE DATABASE defectguru;"
```

## 📊 Key Files Modified

- `app/Http/Controllers/InspectionController.php` - Location-specific mapping
- `resources/views/inspections/pdf_template_2.blade.php` - Template fix
- `resources/css/app.css` - Professional styling
- `resources/views/layouts/navigation.blade.php` - Branding enhancement

## 📝 Version Info

- **Version**: 2.0.1
- **Status**: Production Ready
- **Laravel**: 11.x
- **Last Updated**: August 2026

## 🛠️ Support

For issues or support, check:
- `storage/logs/laravel.log` for error logs
- `.env` file configuration
- Database connection settings

---

**DefectGuru** - Making property inspections professional and efficient.
