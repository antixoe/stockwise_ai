<?php

namespace App\Policies;

use App\Models\Recommendation;
use App\Models\User;

class RecommendationPolicy
{
    public function viewAny(User $user): bool { return $user->is_active && $user->hasRole('admin', 'manager'); }
    public function view(User $user, Recommendation $recommendation): bool { return $this->viewAny($user); }
    public function decide(User $user, Recommendation $recommendation): bool { return $user->is_active && $user->hasRole('manager') && $recommendation->status === 'pending'; }
}
