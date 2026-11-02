<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditLogService
{
    public function record(string $action, ?Model $auditable = null, array $old = [], array $new = [], ?Request $request = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $request?->user()?->id ?? auth()->id(),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'request_id' => $request?->header('X-Request-ID', (string) Str::uuid()),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'old_values' => $old,
            'new_values' => $this->redact($new),
        ]);
    }

    private function redact(array $values): array
    {
        foreach (['password', 'password_confirmation', 'token', 'secret'] as $key) {
            if (array_key_exists($key, $values)) {
                $values[$key] = '[REDACTED]';
            }
        }

        return $values;
    }
}
