<?php

use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\RepaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ── Public (tanpa login) ──────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// ── Protected (harus login) ───────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('members', MemberController::class);

    Route::resource('loans', LoanController::class)->except(['edit', 'update', 'destroy']);
    Route::delete('loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');
    Route::patch('loans/{loan}/status', [LoanController::class, 'updateStatus'])->name('loans.update-status');
    Route::get('loans/export/excel', [LoanController::class, 'exportExcel'])->name('loans.export-excel');

    Route::get('installments/{installment}/pay',  [RepaymentController::class, 'create'])->name('repayments.create');
    Route::post('installments/{installment}/pay', [RepaymentController::class, 'store'])->name('repayments.store');
    Route::get('repayments', [RepaymentController::class, 'index'])->name('repayments.index');

    Route::get('cash-flows',         [CashFlowController::class, 'index'])->name('cash-flows.index');
    Route::get('cash-flows/create',  [CashFlowController::class, 'create'])->name('cash-flows.create');
    Route::post('cash-flows',        [CashFlowController::class, 'store'])->name('cash-flows.store');
    Route::get('cash-flows/export/excel',     [CashFlowController::class, 'exportExcel'])->name('cash-flows.export-excel');
    Route::get('cash-flows/export/recap-pdf', [CashFlowController::class, 'exportRecapPdf'])->name('cash-flows.export-recap-pdf');

    Route::patch('loans/installments/{installment}/due-date', [LoanController::class, 'updateInstallmentDueDate'])
    ->name('loans.installments.update-due-date');

    Route::post('loans/{loan}/repayments/bulk', [RepaymentController::class, 'bulkStore'])
    ->name('repayments.bulk-store');

    Route::delete('/repayments/{repayment}', [RepaymentController::class, 'destroy'])
    ->name('repayments.destroy');
});