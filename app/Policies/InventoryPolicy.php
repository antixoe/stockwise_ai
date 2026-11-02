<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Inventory;

class InventoryPolicy
{
    public function viewAny(User $user): bool { return $user->is_active; }
    public function view(User $user, Inventory $inventory): bool { return $user->is_active; }
}
