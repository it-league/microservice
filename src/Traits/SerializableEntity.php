<?php


namespace ITLeague\Microservice\Traits;


use Closure;
use Opis\Closure\SerializableClosure;

trait SerializableEntity
{

    public function __sleep(): array
    {
        foreach ($this->filters as &$filter) {
            if ($filter instanceof Closure) {
                $filter = new SerializableClosure($filter);
            }
        }

        return parent::__sleep();
    }

    public function __wakeup(): void
    {
        foreach ($this->filters as &$filter) {
            if ($filter instanceof SerializableClosure) {
                $filter = $filter->getClosure();
            }
        }

        parent::__wakeup();
    }
}
