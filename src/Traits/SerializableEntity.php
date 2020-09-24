<?php


namespace ITLeague\Microservice\Traits;


use Closure;
use Opis\Closure\SerializableClosure;

trait SerializableEntity
{

    public function __sleep()
    {
        foreach ($this->filters as &$filter) {
            if ($filter instanceof Closure) {
                $filter = new SerializableClosure($filter);
            }
        }

        return parent::__sleep();
    }

    public function __wakeup()
    {
        foreach ($this->filters as &$filter) {
            if ($filter instanceof SerializableClosure) {
                $filter = $filter->getClosure();
            }
        }

        parent::__wakeup();
    }
}
