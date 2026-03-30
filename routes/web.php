<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\DynamicCrudController;
use App\Http\Controllers\TableBuilderController;
use App\Http\Controllers\TableColumnController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', UserController::class)->middleware('can:users.view');
    Route::resource('roles', RoleController::class)->middleware('can:roles.view');
    Route::resource('permissions', PermissionController::class)->middleware('can:permissions.view');

    // Profile Management (available for all authenticated users)
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Menu Management
    Route::resource('menus', MenuController::class)->middleware('role:admin');
    Route::put('/menus/{menu}/display-columns', [MenuController::class, 'updateDisplayColumns'])->name('menus.update-display-columns')->middleware('role:admin');

    // Table Builder
    Route::get('/tables/create', [TableBuilderController::class, 'index'])->name('tables.create')->middleware('role:admin');
    Route::post('/tables', [TableBuilderController::class, 'store'])->name('tables.store')->middleware('role:admin');

    // Add Column to Table
    Route::get('/tables/add-column', [TableColumnController::class, 'create'])->name('tables.add-column')->middleware('role:admin');
    Route::post('/tables/add-column', [TableColumnController::class, 'store'])->name('tables.add-column.store')->middleware('role:admin');
    Route::get('/tables/columns', [TableColumnController::class, 'getTableColumns'])->name('tables.columns')->middleware('role:admin');

    // Activity Log
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::get('/activity-log/{subjectType}/{subjectId}', [ActivityLogController::class, 'show'])->name('activity-log.show');

    // Test route for menu creation
    Route::get('/test-menu-create', function () {
        try {
            $menu = \App\Models\Menu::create([
                'name' => 'Products Test',
                'table_name' => 'products',
                'icon' => 'bi-box-seam',
                'order' => 10,
                'is_active' => true,
                'fields' => [
                    ['name' => 'id', 'type' => 'number', 'nullable' => false],
                    ['name' => 'name', 'type' => 'text', 'nullable' => false],
                ],
            ]);

            return "Menu created with ID: {$menu->id}";
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    });

    // Dynamic CRUD
    Route::prefix('crud/{menu}')->name('dynamic.')->group(function () {
        Route::get('/', [DynamicCrudController::class, 'index'])->name('index');
        Route::get('/create', [DynamicCrudController::class, 'create'])->name('create');
        Route::post('/', [DynamicCrudController::class, 'store'])->name('store');
        Route::get('/{id}', [DynamicCrudController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DynamicCrudController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DynamicCrudController::class, 'update'])->name('update');
        Route::delete('/{id}', [DynamicCrudController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [DynamicCrudController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/export', [DynamicCrudController::class, 'export'])->name('export');
    });
});
