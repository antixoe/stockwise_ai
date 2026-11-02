<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    /** @use HasFactory<\Database\Factories\AuditLogFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'action', 'auditable_type', 'auditable_id', 'request_id', 'ip_address', 'user_agent', 'old_values', 'new_values'];
    protected function casts(): array { return ['old_values' => 'array', 'new_values' => 'array']; }
    public function user() { return $this->belongsTo(User::class); }
    public function auditable() { return $this->morphTo(); }
}
