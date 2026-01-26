<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?Model;

    /**
     * Find user by Google ID
     */
    public function findByGoogleId(string $googleId): ?Model;

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection;

    /**
     * Update user status
     */
    public function updateStatus(int $id, bool $status): bool;
}
