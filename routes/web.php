<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\RefBulanController;
use App\Http\Controllers\Admin\AttendanceSummaryController;
use App\Http\Controllers\Admin\AttendanceValidationController;
use App\Http\Controllers\Admin\ReferensiWfoController;
use App\Http\Controllers\Admin\WfoValidationController;
use App\Http\Controllers\Admin\KpiAssessmentController;

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

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('attendance.index');
    }

    return redirect()->route('login');
});

Route::get('/home', function () {
    return redirect()->route('admin.index');
});

// CSRF Token Route untuk auto-refresh
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()])
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get'); // Fallback untuk expired token

    // Attendance Routes (Protected - Only for Employees)
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');

    // Leave Routes
    Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');

    // Profile Routes
    Route::get('/profile/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.change-password');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::get('/profile/change-username', [ProfileController::class, 'showChangeUsernameForm'])->name('profile.change-username');
    Route::post('/profile/change-username', [ProfileController::class, 'changeUsername']);

    // KPI Routes (karyawan penilai)
    Route::get('/kpi', [KpiAssessmentController::class, 'index'])->name('kpi.index');
    Route::get('/kpi/form', [KpiAssessmentController::class, 'form'])->name('kpi.form');
    Route::post('/kpi', [KpiAssessmentController::class, 'store'])->name('kpi.store');
    Route::post('/kpi/indikator', [KpiAssessmentController::class, 'storeUserIndicator'])->name('kpi.indikator.store');
    Route::put('/kpi/indikator', [KpiAssessmentController::class, 'updateUserIndicator'])->name('kpi.indikator.update');
    Route::delete('/kpi/indikator', [KpiAssessmentController::class, 'destroyUserIndicator'])->name('kpi.indikator.destroy');
    Route::get('/kpi/export', [KpiAssessmentController::class, 'export'])->name('kpi.export');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if (auth()->check()) {
            return redirect()->route('attendance.index');
        }

        return redirect()->route('admin.login');
    })->name('index');

    // Admin Auth
    Route::get('/ict-login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/ict-login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AdminAuthController::class, 'logout'])->name('logout.get'); // Fallback untuk expired token

    // Admin Protected Routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/today-attendance', function (\Illuminate\Http\Request $request) {
            return redirect()->route('admin.dashboard', $request->only('date'));
        })->name('today-attendance.index');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::resource('employees', EmployeeController::class);
        Route::post('/employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password');
        Route::post('/employees/{employee}/toggle-jenis', [EmployeeController::class, 'toggleJenis'])->name('employees.toggle-jenis');
        Route::get('/attendance-history', [\App\Http\Controllers\Admin\AttendanceHistoryController::class, 'index'])->name('attendance-history.index');
        Route::get('/attendance-history/export', [\App\Http\Controllers\Admin\AttendanceHistoryController::class, 'export'])->name('attendance-history.export');
        Route::get('/attendance-history/export-monthly', [\App\Http\Controllers\Admin\AttendanceHistoryController::class, 'exportMonthlySummary'])->name('attendance-history.export-monthly');
        Route::get('/attendance-history/{attendanceId}/logs', [\App\Http\Controllers\Admin\AttendanceHistoryController::class, 'getLogs'])->name('attendance-history.logs');
        Route::get('/attendance-summary', [AttendanceSummaryController::class, 'index'])->name('attendance-summary.index');
        Route::get('/attendance-summary/export', [AttendanceSummaryController::class, 'export'])->name('attendance-summary.export');
        Route::get('/attendance-validation', [AttendanceValidationController::class, 'index'])->name('attendance-validation.index');
        Route::post('/attendance-validation/late', [AttendanceValidationController::class, 'validateLate'])->name('attendance-validation.validate-late');
        Route::get('/referensi-wfo', [ReferensiWfoController::class, 'index'])->name('referensi-wfo.index');
        Route::post('/referensi-wfo', [ReferensiWfoController::class, 'save'])->name('referensi-wfo.save');
        Route::get('/wfo-validation', [WfoValidationController::class, 'index'])->name('wfo-validation.index');
        Route::post('/wfo-validation/apply', [WfoValidationController::class, 'apply'])->name('wfo-validation.apply');
        Route::get('/leave-history', [\App\Http\Controllers\Admin\LeaveHistoryController::class, 'index'])->name('leave-history.index');
        Route::put('/leave-history/{leave}', [\App\Http\Controllers\Admin\LeaveHistoryController::class, 'update'])->name('leave-history.update');
        Route::delete('/leave-history/{leave}', [\App\Http\Controllers\Admin\LeaveHistoryController::class, 'destroy'])->name('leave-history.destroy');
        Route::get('/location-settings', [\App\Http\Controllers\Admin\LocationController::class, 'index'])->name('location.index');
        Route::put('/location-settings', [\App\Http\Controllers\Admin\LocationController::class, 'update'])->name('location.update');
        Route::get('/holiday', [\App\Http\Controllers\Admin\HolidayController::class, 'index'])->name('holiday.index');
        Route::post('/holiday', [\App\Http\Controllers\Admin\HolidayController::class, 'store'])->name('holiday.store');
        Route::post('/holiday/sync', [\App\Http\Controllers\Admin\HolidayController::class, 'syncFromApi'])->name('holiday.sync');
        Route::put('/holiday/{id}', [\App\Http\Controllers\Admin\HolidayController::class, 'update'])->name('holiday.update');
        Route::delete('/holiday/{id}', [\App\Http\Controllers\Admin\HolidayController::class, 'destroy'])->name('holiday.destroy');
        Route::post('/ref-bulan/refresh', [RefBulanController::class, 'refresh'])->name('ref-bulan.refresh');
        Route::get('/kpi', [KpiAssessmentController::class, 'index'])->name('kpi.index');
        Route::get('/kpi/form', [KpiAssessmentController::class, 'form'])->name('kpi.form');
        Route::post('/kpi', [KpiAssessmentController::class, 'store'])->name('kpi.store');
        Route::get('/kpi/indikator-role', [KpiAssessmentController::class, 'roleIndicators'])->name('kpi.indikator-role');
        Route::post('/kpi/indikator-role', [KpiAssessmentController::class, 'updateRoleIndicators'])->name('kpi.indikator-role.update');
        Route::post('/kpi/indikator', [KpiAssessmentController::class, 'storeUserIndicator'])->name('kpi.indikator.store');
        Route::put('/kpi/indikator', [KpiAssessmentController::class, 'updateUserIndicator'])->name('kpi.indikator.update');
        Route::delete('/kpi/indikator', [KpiAssessmentController::class, 'destroyUserIndicator'])->name('kpi.indikator.destroy');
        Route::get('/kpi/export', [KpiAssessmentController::class, 'export'])->name('kpi.export');
    });
});
