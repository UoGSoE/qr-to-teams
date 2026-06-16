<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\SSOController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [MessageController::class, 'show'])->name('message');

// Login routes - shows login page with both local and SSO options
Route::middleware('guest')->group(function () {
    // Redirects to our login page if not authenticated
    // Route::get('/', function () {
    //     return redirect()->route('login');
    // });

    // This is our own log in page - ideally with an option to log in locally for local/dev - and of course the "Login with SSO" button
    Route::get('/login', [SSOController::class, 'login'])->name('login');
    // Or as a Livewire component if you prefer
    // Route::get('/login', App\Livewire\Login::class)->name('login');
});

// SSO specific routes
Route::post('/login', [SSOController::class, 'localLogin'])->name('login.local');
Route::get('/login/sso', [SSOController::class, 'ssoLogin'])->name('login.sso');
Route::get('/auth/callback', [SSOController::class, 'handleProviderCallback'])->name('sso.callback');
Route::post('/logout', [SSOController::class, 'logout'])->name('auth.logout');
Route::get('/logged-out', [SSOController::class, 'loggedOut'])->name('logged_out');

Route::get('/form', [FormController::class, 'create'])->name('form')->middleware('throttle:10,1');
Route::post('/form', [FormController::class, 'store'])->name('form.submit')->middleware('throttle:10,1');

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('user.index');
});
