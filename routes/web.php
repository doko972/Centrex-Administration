<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CentrexController;
use App\Http\Controllers\Admin\ClientCentrexController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Client\NginxProxyController;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Si l'utilisateur est connecté, rediriger selon son rôle
    if (Auth::check()) {
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('client.dashboard');
    }

    // Sinon, rediriger vers la page de connexion
    return redirect()->route('login');
});

// Routes d'authentification
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1'); // Max 5 tentatives par minute par IP
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Routes Admin (protégées par middleware 'admin')
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Routes CRUD Clients
    Route::resource('clients', ClientController::class);

    // Routes CRUD Centrex
    Route::resource('centrex', CentrexController::class);

    // Routes pour associer Centrex ↔ Clients
    Route::get('/clients/{client}/manage-centrex', [ClientCentrexController::class, 'manage'])->name('clients.manage-centrex');
    Route::post('/clients/{client}/manage-centrex', [ClientCentrexController::class, 'update'])->name('clients.update-centrex');
});

/*
|--------------------------------------------------------------------------
| Routes Client (protégées par middleware 'client')
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Accès direct (ancienne méthode)
    Route::get('/centrex/{centrex}/access', [\App\Http\Controllers\Client\CentrexAccessController::class, 'access'])->name('centrex.access');
    
    // Routes pour le reverse proxy Laravel
    Route::get('/centrex/{centrex}/view', [\App\Http\Controllers\Client\CentrexProxyController::class, 'show'])->name('centrex.view');
    Route::any('/centrex/{centrex}/proxy/{any}', [\App\Http\Controllers\Client\CentrexProxyController::class, 'proxy'])
        ->where('any', '.*')
        ->name('centrex.proxy');
    // Route pour le proxy sans chemin (page d'accueil)
    Route::any('/centrex/{centrex}/proxy', [\App\Http\Controllers\Client\CentrexProxyController::class, 'proxy'])
        ->name('centrex.proxy.root');
    
    // NOUVEAU : Proxy Nginx (meilleure solution)
    Route::get('/centrex/{centrex}/nginx-proxy', [NginxProxyController::class, 'show'])
        ->name('centrex.nginx-proxy');
});
