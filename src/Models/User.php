<?php

namespace ITLeague\Microservice\Models;

use Illuminate\Auth\GenericUser;

/**
 * ITLeague\Microservice\User
 *
 * @property int $id
 * @property array $scope
 */

class User extends GenericUser
{
    public function hasScope($scope): bool
    {
        return collect($this->scope)->contains($scope);
    }

    public function isAdmin(): bool
    {
        return $this->hasScope('admin') || $this->hasScope('super-admin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasScope('super-admin');
    }
}
