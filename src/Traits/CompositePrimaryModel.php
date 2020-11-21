<?php


namespace ITLeague\Microservice\Traits;


trait CompositePrimaryModel
{
    /**
     * Set the keys for a select query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSelectQuery($query)
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
     * @return mixed
     */
    protected function getKeyForSelectQuery(?string $keyName = null)
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
    protected function setKeysForSaveQuery($query)
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
    protected function getKeyForSaveQuery(?string $keyName = null)
    {
        $keyName = $keyName ?? $this->getKeyName();
        return $this->original[$keyName] ?? $this->getAttribute($keyName);
    }
}
