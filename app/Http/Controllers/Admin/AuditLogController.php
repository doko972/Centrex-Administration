<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Afficher le journal d'audit
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $action = trim((string) $request->query('action', ''));

        $logs = AuditLog::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%");
                });
            })
            ->when($action !== '', function ($query) use ($action) {
                $query->where('action', $action);
            })
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-log.index', compact('logs', 'search', 'action', 'actions'));
    }
}
