<?php

namespace Based\Fluent;

use Based\Fluent\Casts\AbstractRelation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

/** @mixin \Based\Fluent\Fluent */
trait HasRelations
{
    protected Collection $fluentRelations;

    /**
     * Get relations defined as public properties
     *
     * @return \Illuminate\Support\Collection<ReflectionProperty>|ReflectionProperty[]
     */
    public function getFluentRelations(): Collection
    {
        if (isset($this->fluentRelations)) {
            return $this->fluentRelations;
        }

        $reflection = new ReflectionClass($this);

        return $this->fluentRelations = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
            ->filter(fn (ReflectionProperty $property) => $property->class === self::class)
            ->filter(function (ReflectionProperty $property) {
                $attributes = collect($property->getAttributes());

                return is_subclass_of($property->getType()->getName(), Model::class)
                    || $attributes->contains(function (ReflectionAttribute $attribute) {
                        return is_subclass_of($attribute->getName(), AbstractRelation::class);
                    });
            });
    }

    /**
     * Get fluently defined relation
     * 
     * @param  string  $key 
     * @return null|\ReflectionProperty 
     */
    public function getFluentRelation(string $key): ?ReflectionProperty
    {
        return $this->getFluentRelations()
            ->filter(fn (ReflectionProperty $property) => $property->getName() === $key)
            ->first();
    }

    /**
     * Overload the method to populate public property
     * Set the given relationship on the model.
     *
     * @param  string  $relation
     * @param  mixed  $value
     * @return $this
     */
    public function setRelation($relation, $value)
    {
        $this->relations[$relation] = $value;

        $fluentRelation = $this->getFluentRelation($relation);
        $fluentRelationType = $fluentRelation->getType();

        if ($fluentRelation && ($fluentRelationType->allowsNull() || !is_null($value))) {
            $this->{$relation} = $fluentRelationType->getName() == Collection::class
                ? collect($value)
                : $value;
        }

        return $this;
    }

    /**
     * Overload the method to unset public property
     * Unset a loaded relationship.
     *
     * @param  string  $relation
     * @return $this
     */
    public function unsetRelation($relation)
    {
        unset($this->relations[$relation]);

        if ($this->getFluentRelation($relation)) {
            unset($this->{$relation});
        }

        return $this;
    }

    /**
     * Overload the method to populate public properties
     * Set the entire relations array on the model.
     *
     * @param  array  $relations
     * @return $this
     */
    public function setRelations(array $relations)
    {
        foreach ($relations as $relation => $value) {
            $this->setRelation($relation, $value);
        }

        return $this;
    }

    /**
     * Overload the method to unset public properties
     * Unset all the loaded relations for the instance.
     *
     * @return $this
     */
    public function unsetRelations()
    {
        foreach (array_keys($this->relations) as $relation) {
            if ($this->getFluentRelation($relation)) {
                unset($this->{$relation});
            }
        }

        $this->relations = [];

        return $this;
    }
}
