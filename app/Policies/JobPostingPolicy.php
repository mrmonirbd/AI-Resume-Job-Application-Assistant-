<?php

namespace App\Policies;

use App\Models\JobPosting;
use App\Models\User;

class JobPostingPolicy
{
    public function view(User $user, JobPosting $jobPosting): bool
    {
        return $jobPosting->user_id === $user->id;
    }

    public function update(User $user, JobPosting $jobPosting): bool
    {
        return $this->view($user, $jobPosting);
    }

    public function delete(User $user, JobPosting $jobPosting): bool
    {
        return $this->view($user, $jobPosting);
    }
}
