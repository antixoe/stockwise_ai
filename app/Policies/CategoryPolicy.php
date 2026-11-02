<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
{
    public function before(User $user): ?bool { return $user->hasRole('admin') ? true : null; }
    public function viewAny(User $user): bool { return $user->is_active; }
    public function view(User $user, Category $category): bool { return $user->is_active; }
    public function create(User $user): bool { return $user->is_active; }
    public function update(User $user, Category $category): bool { return $user->is_active; }
    public function delete(User $user, Category $category): bool { return false; }
}
