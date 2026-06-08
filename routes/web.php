<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\PaymentPointController;
use App\Http\Controllers\LaneController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\TariffController;
use App\Http\Controllers\TransponderController;
use App\Http\Controllers\StatisticsController;

// Public routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Profile (any authenticated user)
Route::get('/profile', [ProfileController::class, 'index'])->name('profile')
    ->middleware('auth.driver');
Route::post('/profile/balance', [ProfileController::class, 'addBalance'])->name('profile.balance')
    ->middleware('auth.driver');

// Admin-only routes (driver id=1)
Route::middleware(['auth.driver', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('drivers')->name('drivers.')->group(function () {
        Route::get('/', [DriverController::class, 'index'])->name('index');
        Route::get('/create', [DriverController::class, 'create'])->name('create');
        Route::post('/', [DriverController::class, 'store'])->name('store');
        Route::get('/{driver}/edit', [DriverController::class, 'edit'])->name('edit');
        Route::put('/{driver}', [DriverController::class, 'update'])->name('update');
        Route::delete('/{driver}', [DriverController::class, 'destroy'])->name('destroy');
        Route::get('/{driver}/card', [DriverController::class, 'card'])->name('card');
        Route::post('/{driver}/balance', [DriverController::class, 'addBalance'])->name('balance');
        Route::get('/data', [DriverController::class, 'getTableData'])->name('data');
    });

    Route::prefix('vehicles')->name('vehicles.')->group(function () {
        Route::get('/', [VehicleController::class, 'index'])->name('index');
        Route::post('/', [VehicleController::class, 'store'])->name('store');
        Route::put('/{vehicle}', [VehicleController::class, 'update'])->name('update');
        Route::delete('/{vehicle}', [VehicleController::class, 'destroy'])->name('destroy');
        Route::get('/{vehicle}/data', [VehicleController::class, 'getData'])->name('data');
    });

    Route::prefix('payment-points')->name('payment_points.')->group(function () {
        Route::get('/', [PaymentPointController::class, 'index'])->name('index');
        Route::post('/', [PaymentPointController::class, 'store'])->name('store');
        Route::put('/{point}', [PaymentPointController::class, 'update'])->name('update');
        Route::delete('/{point}', [PaymentPointController::class, 'destroy'])->name('destroy');
        Route::get('/{point}/lanes', [LaneController::class, 'index'])->name('lanes');
        Route::post('/lanes', [LaneController::class, 'store'])->name('lanes.store');
        Route::put('/lanes/{lane}', [LaneController::class, 'update'])->name('lanes.update');
        Route::delete('/lanes/{lane}', [LaneController::class, 'destroy'])->name('lanes.destroy');
        Route::get('/lanes/{lane}', [LaneController::class, 'show'])->name('lanes.show');
    });

    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::get('/lanes-by-point/{point}', [TransactionController::class, 'getLanesByPoint'])->name('lanes-by-point');
    });

    Route::prefix('fines')->name('fines.')->group(function () {
        Route::get('/', [FineController::class, 'index'])->name('index');
        Route::post('/', [FineController::class, 'store'])->name('store');
        Route::post('/{fine}/pay', [FineController::class, 'pay'])->name('pay');
        Route::delete('/{fine}', [FineController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('tariffs')->name('tariffs.')->group(function () {
        Route::get('/', [TariffController::class, 'index'])->name('index');
        Route::post('/', [TariffController::class, 'store'])->name('store');
        Route::put('/{tariff}', [TariffController::class, 'update'])->name('update');
        Route::delete('/{tariff}', [TariffController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('transponders')->name('transponders.')->group(function () {
        Route::get('/', [TransponderController::class, 'index'])->name('index');
        Route::post('/', [TransponderController::class, 'store'])->name('store');
        Route::put('/{transponder}', [TransponderController::class, 'update'])->name('update');
        Route::delete('/{transponder}', [TransponderController::class, 'destroy'])->name('destroy');
        Route::get('/generate', [TransponderController::class, 'generateSerial'])->name('generate');
    });

    Route::prefix('statistics')->name('statistics.')->group(function () {
        Route::get('/', [StatisticsController::class, 'index'])->name('index');
        Route::get('/data', [StatisticsController::class, 'getData'])->name('data');
        Route::get('/export', [StatisticsController::class, 'exportCsv'])->name('export');
    });
});
