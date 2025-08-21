<?php

namespace App\Policies;

use App\Models\KbCategory;
use App\Models\User;

class KbCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('kb.view');
    }

    public function view(User $user, KbCategory $kbCategory): bool
    {
        return $user->can('kb.view');
    }

    public function create(User $user): bool
    {
        return $user->can('kb.categories.manage');
    }

    public function update(User $user, KbCategory $kbCategory): bool
    {
        return $user->can('kb.categories.manage');
    }

    public function delete(User $user, KbCategory $kbCategory): bool
    {
        return $user->can('kb.categories.manage');
    }
}
