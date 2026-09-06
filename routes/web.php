<?php

use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\InvoiceController; // Ditambah untuk Modul Invoice
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
Route::get('/', function () {
    return view('welcome');
});

// Dashboard Utama Projek Anda
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Kumpulan Laluan yang Dilindungi Middleware 'auth'
Route::middleware('auth')->group(function () {

    Route::resource('users', UserController::class);
    
    // Modul Profile (Bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Modul Inspection
    Route::get('/inspections/create', [InspectionController::class, 'create'])->name('inspection.create');
    Route::post('/inspections', [InspectionController::class, 'store'])->name('inspection.store');
    Route::get('/inspections', [InspectionController::class, 'index'])->name('inspection.index');

    Route::resource('inspection', InspectionController::class);
    
    // Route untuk View dan Edit
    Route::get('/inspections/{inspection}', [InspectionController::class, 'show'])->name('inspection.show');
    Route::get('/inspections/{inspection}/edit', [InspectionController::class, 'edit'])->name('inspection.edit');
    Route::put('/inspections/{inspection}', [InspectionController::class, 'update'])->name('inspection.update');
    
    // Route untuk Delete Inspection
    Route::delete('/inspections/{inspection}', [InspectionController::class, 'destroy'])->name('inspection.destroy');

    // Route untuk Defect Rapid Entry
    Route::get('/inspections/{inspection}/add-defects', [\App\Http\Controllers\DefectController::class, 'createRapid'])->name('defects.rapid');
    Route::post('/inspections/{inspection}/defects/store', [\App\Http\Controllers\DefectController::class, 'storeRapid'])->name('defects.storeRapid');

    // Route untuk Edit, Update & Delete Defect
    Route::get('/defects/{defect}/edit', [\App\Http\Controllers\DefectController::class, 'edit'])->name('defects.edit');
    Route::post('/defects/{defect}/update', [\App\Http\Controllers\DefectController::class, 'update'])->name('defects.update');
    Route::delete('/defects/{defect}', [\App\Http\Controllers\DefectController::class, 'destroy'])->name('defects.destroy');
    
    // Route Download PDF Inspection (Dibuang template_type kerana sudah dinamik ikut database)
    Route::get('/inspection/{id}/download-pdf', [InspectionController::class, 'downloadPDF'])->name('inspection.pdf');

    // ==========================================
    // Modul Invoice & Cash Flow
    // ==========================================
Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    
    // Route Download PDF Invoice
    Route::get('/invoice/{id}/pdf', [InvoiceController::class, 'downloadPDF'])->name('invoice.pdf');

    // ==========================================
    // Modul Cash Flow
    // ==========================================
    Route::get('/cashflow', [CashFlowController::class, 'index'])->name('cashflow.index');
    Route::get('/cashflow/create', [CashFlowController::class, 'create'])->name('cashflow.create');
    Route::post('/cashflow', [CashFlowController::class, 'store'])->name('cashflow.store');
    Route::get('/cashflow/{id}/pdf', [CashFlowController::class, 'downloadPDF'])->name('cashflow.pdf');
    Route::get('/cashflow/report-pdf', [CashFlowController::class, 'monthlyReportPDF'])->name('cashflow.report_pdf');

    Route::get('/reports/part-time', [ReportController::class, 'partTimeWages'])->name('reports.part_time');
});

// Fail laluan pengesahan rasmi Laravel Breeze (Login, Logout, dll)
require __DIR__.'/auth.php';