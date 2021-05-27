<?php


namespace ITLeague\Microservice\Traits;


use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use ReflectionClass;

trait SerializesEntity
{

    use SerializesModels {
        SerializesModels::__serialize as model__serialize;
        SerializesModels::__unserialize as model__unserialize;
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        $values = $this->model__serialize();

        $properties = (new ReflectionClass($this))->getProperties();

        $class = get_class($this);

        foreach ($properties as $property) {
            $name = $property->getName();
            if (in_array($name, self::closureProperties)) {
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
     */
    public function __unserialize(array $values): array
    {
        $properties = (new ReflectionClass($this))->getProperties();

        $class = get_class($this);
        $closurePropertyNames = [];

        foreach ($properties as $property) {
            $name = $property->getName();

            if (in_array($name, self::closureProperties)) {
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
                $closurePropertyNames[] = $name;
            }
        }

        return $this->model__unserialize(Arr::except($values, $closurePropertyNames));
    }

}
