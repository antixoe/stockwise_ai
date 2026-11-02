<?php

namespace App\Services;

use App\Models\Recommendation;
use App\Models\RecommendationDecision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecommendationDecisionService
{
    public function decide(Recommendation $recommendation, User $user, string $decision, ?float $finalQuantity, ?string $comment): RecommendationDecision
    {
        if (! $user->hasRole('manager')) throw new InvalidArgumentException('Hanya manajer yang dapat memutuskan rekomendasi.');
        if (! in_array($decision, ['approved', 'rejected'], true)) throw new InvalidArgumentException('Keputusan tidak valid.');
        return DB::transaction(function () use ($recommendation, $user, $decision, $finalQuantity, $comment) {
            $locked = Recommendation::whereKey($recommendation->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending' || $locked->decision()->exists()) throw new InvalidArgumentException('Rekomendasi sudah diputuskan atau tidak lagi pending.');
            $quantity = $decision === 'approved' ? max(0, $finalQuantity ?? (float) $locked->reorder_quantity) : 0;
            $record = RecommendationDecision::create(['recommendation_id' => $locked->id, 'user_id' => $user->id, 'decision' => $decision, 'comment' => $comment, 'decision_snapshot' => ['original_quantity' => (float) $locked->reorder_quantity, 'final_quantity' => $quantity, 'model_version' => data_get($locked->input_snapshot, 'forecast_model_version'), 'rule_version' => $locked->rule_version], 'decided_at' => now()]);
            $locked->update(['status' => $decision]);
            app(AuditLogService::class)->record('recommendation.'.$decision, $locked, ['status' => 'pending'], ['status' => $decision, 'original_quantity' => (float) $locked->reorder_quantity, 'final_quantity' => $quantity, 'reason' => $comment]);
            return $record;
        });
    }
}
