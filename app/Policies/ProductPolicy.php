<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy
{
    public function before(User $user): ?bool { return $user->hasRole('admin') ? true : null; }
    public function viewAny(User $user): bool { return $user->is_active; }
    public function view(User $user, Product $product): bool { return $user->is_active; }
    public function create(User $user): bool { return false; }
    public function update(User $user, Product $product): bool { return false; }
    public function delete(User $user, Product $product): bool { return false; }
}
