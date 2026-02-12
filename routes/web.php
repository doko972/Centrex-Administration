<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CentrexController;
use App\Http\Controllers\Admin\ClientCentrexController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AdminCentrexProxyController;
use App\Http\Controllers\Admin\IpbxController;
use App\Http\Controllers\Client\CentrexProxyController;

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

    // Routes pour associer Centrex ↔ Clients
    Route::get('/clients/{client}/manage-centrex', [ClientCentrexController::class, 'manage'])->name('clients.manage-centrex');
    Route::post('/clients/{client}/manage-centrex', [ClientCentrexController::class, 'update'])->name('clients.update-centrex');

    // Proxy Admin vers FreePBX - DOIT être défini AVANT les routes resource centrex
    Route::get('/centrex/{centrex}/view', [AdminCentrexProxyController::class, 'show'])->name('centrex.view');
    Route::any('/centrex/{centrex}/proxy/{any}', [AdminCentrexProxyController::class, 'proxy'])
        ->where('any', '.*')
        ->name('centrex.proxy');
    Route::any('/centrex/{centrex}/proxy', [AdminCentrexProxyController::class, 'proxy'])
        ->name('centrex.proxy.root');

    // Fallback : capturer les URLs mal formées (sans /proxy/) et les rediriger
    Route::any('/centrex/{centrex_id}/{any}', function ($centrex_id, $any, \Illuminate\Http\Request $request) {
        $query = $request->getQueryString();
        $url = "/admin/centrex/{$centrex_id}/proxy/admin/{$any}" . ($query ? "?{$query}" : "");
        return redirect($url);
    })->where('any', '(?!view|proxy|create|edit).*');

    // Routes CRUD Centrex (après les routes proxy pour éviter les conflits)
    Route::resource('centrex', CentrexController::class);

    // Routes CRUD IPBX
    Route::resource('ipbx', IpbxController::class);
    Route::post('/ipbx/{ipbx}/ping', [IpbxController::class, 'ping'])->name('ipbx.ping');
});

/*
|--------------------------------------------------------------------------
| Routes Client (protégées par middleware 'client')
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Proxy Laravel vers FreePBX
    Route::get('/centrex/{centrex}/view', [CentrexProxyController::class, 'show'])->name('centrex.view');
    Route::any('/centrex/{centrex}/proxy/{any}', [CentrexProxyController::class, 'proxy'])
        ->where('any', '.*')
        ->name('centrex.proxy');
    Route::any('/centrex/{centrex}/proxy', [CentrexProxyController::class, 'proxy'])
        ->name('centrex.proxy.root');

    // Fallback : capturer les URLs mal formées (sans /proxy/) et les rediriger
    Route::any('/centrex/{centrex_id}/{any}', function ($centrex_id, $any, \Illuminate\Http\Request $request) {
        $query = $request->getQueryString();
        $url = "/client/centrex/{$centrex_id}/proxy/admin/{$any}" . ($query ? "?{$query}" : "");
        return redirect($url);
    })->where('any', '(?!view|proxy).*');
});
