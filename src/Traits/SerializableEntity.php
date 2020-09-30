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

        foreach ($this->sorts as &$sort) {
            if ($sort instanceof Closure) {
                $sort = new SerializableClosure($sort);
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

        foreach ($this->sorts as &$sort) {
            if ($sort instanceof SerializableClosure) {
                $sort = $sort->getClosure();
            }
        }

        parent::__wakeup();
    }
}
