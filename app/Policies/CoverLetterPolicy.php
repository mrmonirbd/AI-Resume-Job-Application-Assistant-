<?php

namespace App\Policies;

use App\Models\CoverLetter;
use App\Models\User;

class CoverLetterPolicy
{
    public function view(User $user, CoverLetter $coverLetter): bool
    {
        return $coverLetter->user_id === $user->id;
    }

    public function update(User $user, CoverLetter $coverLetter): bool
    {
        return $this->view($user, $coverLetter);
    }

    public function delete(User $user, CoverLetter $coverLetter): bool
    {
        return $this->view($user, $coverLetter);
    }
}
