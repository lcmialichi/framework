<?php

namespace App\ORM;

class Reflection
{
    private \ReflectionClass $reflection;

    public function __construct(private object $instance)
    {
        $this->reflection = new \ReflectionClass($instance);
    }

    public function getClassAttribute(string $attribute, ?callable $resolver = null): array
    {
        $attributes = $this->mapAttributeInstance($this->reflaction()->getAttributes($attribute));
        if (!$resolver) {
            return $attributes;
        }

        return array_map($resolver, $attributes);
    }

    public function getPropertyAttribute(string $attribute): array
    {
        $properties = $this->reflaction()->getProperties();
        $values = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $values[$property->getName()] = $this->mapAttributeInstance($property->getAttributes($attribute));
        }

        return $values;
    }

    private function reflaction(): \ReflectionClass
    {
        return $this->reflection;
    }

    public function getShortName(): string
    {
        return $this->reflaction()->getShortName();
    }

    /** @param array<\ReflectionAttribute> */
    private function mapAttributeInstance(array $attributes): array
    {
        return array_map(
            function ($attribute) {
                return $attribute->newInstance();
            }, $attributes
        );
    }
}
