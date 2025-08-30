<?php

namespace App\Policies;

use App\Models\User;
use App\Models\KbArticle;

class KbArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('kb.view');
    }

    public function view(User $user, KbArticle $article): bool
    {
        return $user->can('kb.view');
    }

    public function create(User $user): bool
    {
        return $user->can('kb.create');
    }

    public function update(User $user, KbArticle $article): bool
    {
        return $user->can('kb.update');
    }

    public function delete(User $user, KbArticle $article): bool
    {
        return $user->can('kb.delete');
    }

    public function restore(User $user, KbArticle $article): bool
    {
        return $user->can('kb.update');
    }

    public function forceDelete(User $user, KbArticle $article): bool
    {
        return $user->can('kb.delete');
    }
}
