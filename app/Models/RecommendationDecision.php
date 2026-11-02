<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecommendationDecision extends Model
{
    /** @use HasFactory<\Database\Factories\RecommendationDecisionFactory> */
    use HasFactory;

    protected $fillable = ['recommendation_id', 'user_id', 'decision', 'comment', 'decision_snapshot', 'decided_at'];
    protected function casts(): array { return ['decision_snapshot' => 'array', 'decided_at' => 'datetime']; }
    public function recommendation() { return $this->belongsTo(Recommendation::class); }
    public function user() { return $this->belongsTo(User::class); }
}
