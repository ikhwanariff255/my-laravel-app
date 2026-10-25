<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\InvoiceController; // Ditambah untuk Modul Invoice
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Halaman Utama (Welcome)
Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');

// Dashboard Utama Projek Anda (Ganti view asal Breeze dengan Controller anda)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Kumpulan Laluan yang Dilindungi Middleware 'auth'
Route::middleware('auth')->group(function () {

    Route::resource('users', UserController::class)->middleware('can:manage-system');
    // Modul Profile (Bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Modul Inspection
    Route::get('/inspections/create', [InspectionController::class, 'create'])->name('inspection.create');
    Route::post('/inspections', [InspectionController::class, 'store'])->name('inspection.store');
    Route::get('/inspections', [InspectionController::class, 'index'])->name('inspection.index');

    Route::resource('inspection', InspectionController::class);
    
    // Route Baru untuk View dan Edit
    Route::get('/inspections/{inspection}', [InspectionController::class, 'show'])->name('inspection.show');
    Route::get('/inspections/{inspection}/edit', [InspectionController::class, 'edit'])->name('inspection.edit');
    Route::put('/inspections/{inspection}', [InspectionController::class, 'update'])->name('inspection.update');
    
    // --> TAMBAH BARIS INI SUPAYA BUTTON DELETE INSPECTION BERFUNGSI <--
    Route::delete('/inspections/{inspection}', [InspectionController::class, 'destroy'])->name('inspection.destroy');

    // Route untuk Defect Rapid Entry
    Route::get('/inspections/{inspection}/add-defects', [\App\Http\Controllers\DefectController::class, 'createRapid'])->name('defects.rapid');
    Route::post('/inspections/{inspection}/defects/store', [\App\Http\Controllers\DefectController::class, 'storeRapid'])->name('defects.storeRapid');

    // Route untuk Edit, Update & Delete Defect
    Route::get('/defects/{defect}/edit', [\App\Http\Controllers\DefectController::class, 'edit'])->name('defects.edit');
    Route::post('/defects/{defect}/update', [\App\Http\Controllers\DefectController::class, 'update'])->name('defects.update');
    Route::delete('/defects/{defect}', [\App\Http\Controllers\DefectController::class, 'destroy'])->name('defects.destroy');
    
    Route::get('/inspection/{id}/download-pdf/{template_type}', [\App\Http\Controllers\InspectionController::class, 'downloadPDF'])->name('inspection.pdf');

    // ==========================================
    // Modul Invoice & Cash Flow
    // ==========================================
    Route::middleware('can:manage-system')->group(function () {
        Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice.index');
        Route::get('/invoice/create', [InvoiceController::class, 'create'])->name('invoice.create');
        Route::post('/invoice', [InvoiceController::class, 'store'])->name('invoice.store');

        // Route Download PDF Invoice
        Route::get('/invoice/{id}/pdf', [InvoiceController::class, 'downloadPDF'])->name('invoice.pdf');

        // ==========================================
        // Modul Cash Flow
        // ==========================================
        Route::get('/cashflow', [CashFlowController::class, 'index'])->name('cashflow.index');
        Route::get('/cashflow/create', [CashFlowController::class, 'create'])->name('cashflow.create');
        Route::post('/cashflow', [CashFlowController::class, 'store'])->name('cashflow.store');
        Route::get('/cashflow/{id}/pdf', [CashFlowController::class, 'downloadPDF'])->name('cashflow.pdf');
        // Route untuk Laporan Cash Flow Bulanan (Format T-Account PDF)
        Route::get('/cashflow/report-pdf', [CashFlowController::class, 'monthlyReportPDF'])->name('cashflow.report_pdf');

        Route::get('/reports/part-time', [ReportController::class, 'partTimeWages'])->name('reports.part_time');
    });

    // ================= ATTENDANCE =================
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
    Route::get('/attendance/monthly', [AttendanceController::class, 'monthly'])->name('attendance.monthly');
    Route::patch('/attendance/office-hours', [AttendanceController::class, 'updateOfficeHours'])->name('attendance.officeHours');

    // ================= LEAVES =================
    // Own leave applications
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');
    Route::get('/leaves/{leave}/attachment', [LeaveController::class, 'attachment'])->name('leaves.attachment');

    // Leave management & approvals (Owner & Admin Office)
    Route::middleware('can:manage-system')->group(function () {
        Route::get('/leaves/manage', [LeaveController::class, 'manage'])->name('leaves.manage');
        Route::patch('/leaves/{leave}/status', [LeaveController::class, 'updateStatus'])->name('leaves.updateStatus');
    });
});

// Fail laluan pengesahan rasmi Laravel Breeze (Login, Logout, dll)
require __DIR__.'/auth.php';