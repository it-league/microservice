<?php


namespace ITLeague\Microservice\Traits\Models;


use Illuminate\Database\Eloquent\Builder;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait WithCompositePrimary
{
    /**
     * Set the keys for a select query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSelectQuery($query): Builder
    {
        $keys = $this->getKeyName();
        if (! is_array($keys)) {
            return parent::setKeysForSelectQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSelectQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a select query.
     *
     * @param string|null $keyName
     *
     * @return mixed
     */
    protected function getKeyForSelectQuery(?string $keyName = null): mixed
    {
        $keyName = $keyName ?? $this->getKeyName();
        return $this->original[$keyName] ?? $this->getAttribute($keyName);
    }

    /**
     * Set the keys for a save update query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query): Builder
    {
        $keys = $this->getKeyName();
        if (! is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param string|null $keyName
     *
     * @return mixed
     */
    protected function getKeyForSaveQuery(?string $keyName = null): mixed
    {
        $keyName = $keyName ?? $this->getKeyName();
        return $this->original[$keyName] ?? $this->getAttribute($keyName);
    }
}
