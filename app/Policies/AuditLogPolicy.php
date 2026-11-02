<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AuditLog;

class AuditLogPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole('admin') && $user->is_active; }
    public function view(User $user, AuditLog $auditLog): bool { return $user->hasRole('admin') && $user->is_active; }
}
