<?php

namespace ITLeague\Microservice\Models;

use Illuminate\Auth\GenericUser;

/**
 * ITLeague\Microservice\User
 *
 * @property int $id
 * @property array $roles
 */
class User extends GenericUser
{
    final public function hasRole($role): bool
    {
        return collect($this->roles ?? [])->contains($role);
    }

    final public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    final public static function fake(): self
    {
        return new static(['id' => '00000000-0000-0000-0000-000000000000']);
    }
}
