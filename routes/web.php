<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Role
    Route::get('role', [RoleController::class, 'index'])->name('role');
    Route::get('role-create', [RoleController::class, 'create'])->name('roleCreate');
    Route::post('role-store', [RoleController::class, 'store'])->name('roleStore');
    Route::get('role-edit/{id}', [RoleController::class, 'edit'])->name('roleEdit');
    Route::post('role-update/{id}', [RoleController::class, 'update'])->name('roleUpdate');
    Route::get('role-delete/{id}', [RoleController::class, 'destroy'])->name('roleDelete');

    //Permission
    Route::get('permission', [PermissionController::class, 'index'])->name('permission');
    Route::get('permission-create', [PermissionController::class, 'create'])->name('permissionCreate');
    Route::post('permission-store', [PermissionController::class, 'store'])->name('permissionStore');
    Route::get('permission-edit/{id}', [PermissionController::class, 'edit'])->name('permissionEdit');
    Route::post('permission-update/{id}', [PermissionController::class, 'update'])->name('permissionUpdate');
    Route::get('permission-delete/{id}', [PermissionController::class, 'destroy'])->name('permissionDelete');

    //User
    Route::get('user', [UserController::class, 'index'])->name('user');
    Route::get('user-create', [UserController::class, 'create'])->name('userCreate');
    Route::post('user', [UserController::class, 'store'])->name('userStore');
    Route::get('user-edit/{id}', [UserController::class, 'edit'])->name('userEdit');
    Route::post('user-update/{id}', [UserController::class, 'update'])->name('userUpdate');
    Route::get('user-delete/{id}', [UserController::class, 'destroy'])->name('userDelete');

    //Company
    Route::get('com', [CompanyController::class, 'index'])->name('company');
    Route::get('com-create', [CompanyController::class, 'create'])->name('companyCreate');
    Route::post('com', [CompanyController::class, 'store'])->name('companyStore');
    Route::get('com-edit/{id}', [CompanyController::class, 'edit'])->name('companyEdit');
    Route::post('com-update/{id}', [CompanyController::class, 'update'])->name('companyUpdate');
    Route::get('com-delete/{id}', [CompanyController::class, 'destroy'])->name('companyDelete');

    //Employee
    Route::get('emp', [EmployeeController::class, 'index'])->name('employee');
    Route::get('emp-create', [EmployeeController::class, 'create'])->name('employeeCreate');
    Route::post('emp', [EmployeeController::class, 'store'])->name('employeeStore');
    Route::get('emp-edit/{id}', [EmployeeController::class, 'edit'])->name('employeeEdit');
    Route::post('/emp-update/{id}', [EmployeeController::class, 'update'])->name('employeeUpdate');
    Route::get('emp-delete/{id}', [EmployeeController::class, 'destroy'])->name('employeeDelete');
});


require __DIR__ . '/auth.php';
