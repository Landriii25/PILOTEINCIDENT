<?php

namespace App\Policies;

use App\Models\KbArticle;
use App\Models\User;

class KbArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('kb.view');
    }

    public function view(User $user, KbArticle $kbArticle): bool
    {
        return $user->can('kb.view');
    }

    public function create(User $user): bool
    {
        return $user->can('kb.create') || $user->can('kb.update');
    }

    public function update(User $user, KbArticle $kbArticle): bool
    {
        return $user->can('kb.update');
    }

    public function delete(User $user, KbArticle $kbArticle): bool
    {
        return $user->can('kb.delete');
    }
}
