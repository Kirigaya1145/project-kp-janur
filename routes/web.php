<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminBookingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

Auth::routes();

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/tentang', [PageController::class, 'tentang'])->name('tentang');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/booking', [BookingController::class, 'create'])->middleware('auth')->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->middleware('auth')->name('booking.store');
Route::get('/booking/sukses', [BookingController::class, 'sukses'])->middleware('auth')->name('booking.sukses');
Route::get('/booking/riwayat', [BookingController::class, 'index'])->middleware('auth')->name('booking.riwayat');
Route::get('/booking/cek', [BookingController::class, 'formCek'])->name('booking.cek');
Route::post('/booking/cek', function (\Illuminate\Http\Request $request) {
    $request->validate(['kode_booking' => ['required', 'string']]);
    return redirect()->route('booking.show', $request->kode_booking);
})->name('booking.cek.submit');
Route::get('/booking/{id}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/booking/{booking}/konfirmasi-penawaran', [BookingController::class, 'confirmOffer'])->middleware('auth')->name('booking.confirm-offer');
Route::post('/invoice/{invoice}/bukti-pembayaran', [BookingController::class, 'uploadPayment'])->middleware('auth')->name('invoice.upload-payment');

Route::middleware(['auth', 'role:admin,staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminBookingController::class, 'dashboard'])->name('dashboard');
    Route::get('/rute', [AdminBookingController::class, 'ruteIndex'])->name('rute.index');
    Route::post('/rute', [AdminBookingController::class, 'ruteStore'])->name('rute.store');
    Route::get('/booking/{booking}', [AdminBookingController::class, 'show'])->name('booking.show');
    Route::post('/booking/{booking}/penawaran', [AdminBookingController::class, 'offer'])->name('booking.offer');
    Route::post('/booking/{booking}/invoice', [AdminBookingController::class, 'invoice'])->name('booking.invoice');
    Route::post('/booking/{booking}/operasional', [AdminBookingController::class, 'operational'])->name('booking.operational');
    Route::post('/booking/{booking}/progress', [AdminBookingController::class, 'updateProgress'])->name('booking.progress');
    Route::post('/bukti-pembayaran/{bukti}/verifikasi', [AdminBookingController::class, 'verifyPayment'])->name('payment.verify');
});
