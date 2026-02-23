<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CentrexController;
use App\Http\Controllers\Admin\ClientCentrexController;
use App\Http\Controllers\Admin\ClientIpbxController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AdminCentrexProxyController;
use App\Http\Controllers\Admin\AdminIpbxProxyController;
use App\Http\Controllers\Admin\IpbxController;
use App\Http\Controllers\Admin\ConnectionTypeController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Client\CentrexProxyController;
use App\Http\Controllers\Client\IpbxProxyController;
use App\Http\Controllers\SuperClient\DashboardController as SuperClientDashboardController;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Si l'utilisateur est connecté, rediriger selon son rôle
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isSuperClient()) {
            return redirect()->route('superclient.dashboard');
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

    // Routes CRUD pour les données de référence
    Route::resource('connection-types', ConnectionTypeController::class)->except(['show']);
    Route::resource('providers', ProviderController::class)->except(['show']);
    Route::resource('equipment', EquipmentController::class)->except(['show']);

    // Routes pour associer Centrex ↔ Clients
    Route::get('/clients/{client}/manage-centrex', [ClientCentrexController::class, 'manage'])->name('clients.manage-centrex');
    Route::post('/clients/{client}/manage-centrex', [ClientCentrexController::class, 'update'])->name('clients.update-centrex');

    // Routes pour associer IPBX ↔ Clients
    Route::get('/clients/{client}/manage-ipbx', [ClientIpbxController::class, 'manage'])->name('clients.manage-ipbx');
    Route::post('/clients/{client}/manage-ipbx', [ClientIpbxController::class, 'update'])->name('clients.update-ipbx');

    // Proxy Admin vers FreePBX (Centrex) - DOIT être défini AVANT les routes resource centrex
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

    // Proxy Admin vers FreePBX (IPBX) - DOIT être défini AVANT les routes resource ipbx
    Route::get('/ipbx/{ipbx}/view', [AdminIpbxProxyController::class, 'show'])->name('ipbx.view');
    Route::any('/ipbx/{ipbx}/proxy/{any}', [AdminIpbxProxyController::class, 'proxy'])
        ->where('any', '.*')
        ->name('ipbx.proxy');
    Route::any('/ipbx/{ipbx}/proxy', [AdminIpbxProxyController::class, 'proxy'])
        ->name('ipbx.proxy.root');

    // Fallback IPBX : capturer les URLs mal formées
    Route::any('/ipbx/{ipbx_id}/{any}', function ($ipbx_id, $any, \Illuminate\Http\Request $request) {
        $query = $request->getQueryString();
        $url = "/admin/ipbx/{$ipbx_id}/proxy/admin/{$any}" . ($query ? "?{$query}" : "");
        return redirect($url);
    })->where('any', '(?!view|proxy|create|edit|show|ping).*');

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

    // Proxy Laravel vers FreePBX (Centrex)
    Route::get('/centrex/{centrex}/view', [CentrexProxyController::class, 'show'])->name('centrex.view');
    Route::any('/centrex/{centrex}/proxy/{any}', [CentrexProxyController::class, 'proxy'])
        ->where('any', '.*')
        ->name('centrex.proxy');
    Route::any('/centrex/{centrex}/proxy', [CentrexProxyController::class, 'proxy'])
        ->name('centrex.proxy.root');

    // Fallback Centrex : capturer les URLs mal formées (sans /proxy/) et les rediriger
    Route::any('/centrex/{centrex_id}/{any}', function ($centrex_id, $any, \Illuminate\Http\Request $request) {
        $query = $request->getQueryString();
        $url = "/client/centrex/{$centrex_id}/proxy/admin/{$any}" . ($query ? "?{$query}" : "");
        return redirect($url);
    })->where('any', '(?!view|proxy).*');

    // Proxy Laravel vers FreePBX (IPBX)
    Route::get('/ipbx/{ipbx}/view', [IpbxProxyController::class, 'show'])->name('ipbx.view');
    Route::any('/ipbx/{ipbx}/proxy/{any}', [IpbxProxyController::class, 'proxy'])
        ->where('any', '.*')
        ->name('ipbx.proxy');
    Route::any('/ipbx/{ipbx}/proxy', [IpbxProxyController::class, 'proxy'])
        ->name('ipbx.proxy.root');

    // Fallback IPBX : capturer les URLs mal formées
    Route::any('/ipbx/{ipbx_id}/{any}', function ($ipbx_id, $any, \Illuminate\Http\Request $request) {
        $query = $request->getQueryString();
        $url = "/client/ipbx/{$ipbx_id}/proxy/admin/{$any}" . ($query ? "?{$query}" : "");
        return redirect($url);
    })->where('any', '(?!view|proxy).*');
});

/*
|--------------------------------------------------------------------------
| Routes SuperClient (protégées par middleware 'superclient')
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'superclient'])->prefix('superclient')->name('superclient.')->group(function () {
    Route::get('/dashboard', [SuperClientDashboardController::class, 'index'])->name('dashboard');

    // Proxy vers FreePBX (Centrex) - réutilise le controller client
    Route::get('/centrex/{centrex}/view', [CentrexProxyController::class, 'show'])->name('centrex.view');
    Route::any('/centrex/{centrex}/proxy/{any}', [CentrexProxyController::class, 'proxy'])
        ->where('any', '.*')
        ->name('centrex.proxy');
    Route::any('/centrex/{centrex}/proxy', [CentrexProxyController::class, 'proxy'])
        ->name('centrex.proxy.root');

    // Fallback Centrex
    Route::any('/centrex/{centrex_id}/{any}', function ($centrex_id, $any, \Illuminate\Http\Request $request) {
        $query = $request->getQueryString();
        $url = "/superclient/centrex/{$centrex_id}/proxy/admin/{$any}" . ($query ? "?{$query}" : "");
        return redirect($url);
    })->where('any', '(?!view|proxy).*');

    // Proxy vers FreePBX (IPBX) - réutilise le controller client
    Route::get('/ipbx/{ipbx}/view', [IpbxProxyController::class, 'show'])->name('ipbx.view');
    Route::any('/ipbx/{ipbx}/proxy/{any}', [IpbxProxyController::class, 'proxy'])
        ->where('any', '.*')
        ->name('ipbx.proxy');
    Route::any('/ipbx/{ipbx}/proxy', [IpbxProxyController::class, 'proxy'])
        ->name('ipbx.proxy.root');

    // Fallback IPBX
    Route::any('/ipbx/{ipbx_id}/{any}', function ($ipbx_id, $any, \Illuminate\Http\Request $request) {
        $query = $request->getQueryString();
        $url = "/superclient/ipbx/{$ipbx_id}/proxy/admin/{$any}" . ($query ? "?{$query}" : "");
        return redirect($url);
    })->where('any', '(?!view|proxy).*');
});
