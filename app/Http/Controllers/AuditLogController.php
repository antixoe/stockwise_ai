<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index() { $this->authorize('viewAny', AuditLog::class); return response()->json(AuditLog::with('user')->latest()->paginate(50)); }
}
