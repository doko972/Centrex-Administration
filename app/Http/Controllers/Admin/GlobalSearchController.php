<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Centrex;
use App\Models\Ipbx;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Recherche globale à travers clients, centrex et IPBX
     */
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        $clients = collect();
        $centrex = collect();
        $ipbxs = collect();

        if ($query !== '') {
            $clients = Client::with('user')
                ->where(function ($q) use ($query) {
                    $q->where('company_name', 'like', "%{$query}%")
                        ->orWhere('contact_name', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%")
                        ->orWhereHas('user', function ($userQuery) use ($query) {
                            $userQuery->where('name', 'like', "%{$query}%")
                                ->orWhere('email', 'like', "%{$query}%");
                        });
                })
                ->orderBy('company_name')
                ->limit(15)
                ->get();

            $centrex = Centrex::where('name', 'like', "%{$query}%")
                ->orWhere('ip_address', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->orderBy('name')
                ->limit(15)
                ->get();

            $ipbxs = Ipbx::where('client_name', 'like', "%{$query}%")
                ->orWhere('contact_name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->orWhere('ip_address', 'like', "%{$query}%")
                ->orderBy('client_name')
                ->limit(15)
                ->get();
        }

        $totalResults = $clients->count() + $centrex->count() + $ipbxs->count();

        return view('admin.search.index', compact('query', 'clients', 'centrex', 'ipbxs', 'totalResults'));
    }
}
