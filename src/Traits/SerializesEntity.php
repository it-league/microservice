<?php


namespace ITLeague\Microservice\Traits;


use Arr;
use Illuminate\Queue\SerializesModels;
use ReflectionClass;

trait SerializesEntity
{

    use SerializesModels {
        SerializesModels::__serialize as model__serialize;
        SerializesModels::__unserialize as model__unserialize;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function __serialize()
    {
        $values = $this->model__serialize();

        $properties = (new ReflectionClass($this))->getProperties();

        $class = get_class($this);

        foreach ($properties as $property) {
            $name = $property->getName();
            if (in_array($name, static::closureProperties)) {
                $property->setAccessible(true);

                if (! $property->isInitialized($this)) {
                    continue;
                }

                if ($property->isPrivate()) {
                    $name = "\0{$class}\0{$name}";
                } elseif ($property->isProtected()) {
                    $name = "\0*\0{$name}";
                }

                $values[$name] = \Opis\Closure\serialize(
                    $this->getPropertyValue($property)
                );
            }
        }

        return $values;
    }

    /**
     * @param array $values
     *
     * @return array
     * @throws \ReflectionException
     */
    public function __unserialize(array $values)
    {
        $properties = (new ReflectionClass($this))->getProperties();

        $class = get_class($this);

        foreach ($properties as $property) {
            $name = $property->getName();

            if (in_array($name, static::closureProperties)) {
                if ($property->isPrivate()) {
                    $name = "\0{$class}\0{$name}";
                } elseif ($property->isProtected()) {
                    $name = "\0*\0{$name}";
                }

                if (! array_key_exists($name, $values)) {
                    continue;
                }

                $property->setAccessible(true);

                $property->setValue(
                    $this,
                    \Opis\Closure\unserialize($values[$name])
                );
            }
        }

        return $this->model__unserialize(Arr::except($values, static::closureProperties));
    }

}
