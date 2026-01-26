<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function makeModel(): Model
    {
        return new User();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?Model
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find user by Google ID
     */
    public function findByGoogleId(string $googleId): ?Model
    {
        return $this->model->where('google_id', $googleId)->first();
    }

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection
    {
        return $this->model->where('estado', true)->get();
    }

    /**
     * Update user status
     */
    public function updateStatus(int $id, bool $status): bool
    {
        $user = $this->find($id);

        if (!$user) {
            return false;
        }

        return $user->update(['estado' => $status]);
    }
}
