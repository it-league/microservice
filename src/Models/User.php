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
    final public function hasScope($scope): bool
    {
        return collect($this->scope ?? [])->contains($scope);
    }

    final public function isAdmin(): bool
    {
        return $this->hasScope('admin') || $this->hasScope('super-admin');
    }

    final public function isSuperAdmin(): bool
    {
        return $this->hasScope('super-admin');
    }
}
