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

    final public static function fake(): self
    {
        return new static(['id' => '00000000-0000-0000-0000-000000000000']);
    }

    public function header(): array
    {
        return [
            'x-authenticated-userid' => $this->id,
            'x-authenticated-scope' => trim(collect((array)($this->scope ?? []))->implode(' '))
        ];
    }
}
