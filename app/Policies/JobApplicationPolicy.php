<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    public function view(User $user, JobApplication $jobApplication): bool
    {
        return $jobApplication->user_id === $user->id;
    }

    public function update(User $user, JobApplication $jobApplication): bool
    {
        return $this->view($user, $jobApplication);
    }
}
