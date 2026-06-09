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
    Route::patch('loans/{loan}/status', [LoanController::class, 'updateStatus'])->name('loans.update-status');

    Route::get('installments/{installment}/pay',  [RepaymentController::class, 'create'])->name('repayments.create');
    Route::post('installments/{installment}/pay', [RepaymentController::class, 'store'])->name('repayments.store');
    Route::get('repayments', [RepaymentController::class, 'index'])->name('repayments.index');

    Route::get('cash-flows',         [CashFlowController::class, 'index'])->name('cash-flows.index');
    Route::get('cash-flows/create',  [CashFlowController::class, 'create'])->name('cash-flows.create');
    Route::post('cash-flows',        [CashFlowController::class, 'store'])->name('cash-flows.store');
});