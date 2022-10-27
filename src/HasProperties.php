<?php

namespace Based\Fluent;

use Based\Fluent\Casts\AbstractCaster;
use Based\Fluent\Casts\Cast;
use Based\Fluent\Relations\AbstractRelation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait HasProperties
{
    protected Collection $fluentProperties;
    protected bool $fluentAvoidParentSetAttribute = false;

    public function __construct(array $attributes = [])
    {
        $this->buildFluentDefaults();
        $this->buildFluentCasts();

        parent::__construct($attributes);

        $this->hydrateFluentProperties();
    }

    /**
     * Get public properties.
     *
     * @return \Illuminate\Support\Collection<ReflectionProperty>|ReflectionProperty[]
     */
    public function getFluentProperties(): Collection
    {
        if (isset($this->fluentProperties)) {
            return $this->fluentProperties;
        }

        $reflection = new ReflectionClass($this);

        return $this->fluentProperties = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
            ->filter(fn (ReflectionProperty $property) => $property->getDeclaringClass()->getName() === self::class)
            ->reject(function (ReflectionProperty $property) {
                return collect($property->getDeclaringClass()->getTraits())
                    ->contains(function (ReflectionClass $trait) use ($property) {
                        return collect($trait->getProperties(ReflectionProperty::IS_PUBLIC))
                            ->contains(function (ReflectionProperty $traitProperty) use ($property) {
                                return $traitProperty->getName() === $property->getName();
                            });
                    });
            })
            ->filter(fn (ReflectionProperty $property) => $property->hasType())
            ->reject(function (ReflectionProperty $property) {
                $attributes = collect($property->getAttributes());

                return is_subclass_of($property->getType()->getName(), Model::class)
                    || $attributes->contains(function (ReflectionAttribute $attribute) {
                        return is_subclass_of($attribute->getName(), AbstractRelation::class);
                    });
            });
    }

    /**
     * Overload the method to avoid interfering with model loading
     * Set the array of model attributes. No checking is done.
     *
     * @param  array  $attributes
     * @param  bool  $sync
     * @return $this
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $keys = $this->getFluentProperties()
            ->filter(fn (ReflectionProperty $property) => $property->isInitialized($this))
            ->map(fn (ReflectionProperty $property) => $property->getName())->toArray();

        /*
         * Unset all properties that we're managing, so any current values aren't prioritized
         * over the ones being set here. We would otherwise clobber new values during operations
         * that retreive a model, such as Model::newFromBuilder or Model::refresh.
         */
        foreach ($keys as $key) {
            unset($this->{$key});
        }

        parent::setRawAttributes($attributes, $sync);

        try {
            /*
             * Laravel has already set these attributes internally— avoid redundant calls to
             * parent::setAttribute during this logic (due to property assignment calling __set).
             */
            $this->fluentAvoidParentSetAttribute = true;

            foreach ($keys as $key) {
                $this->{$key} = $this->getAttribute($key);
            }
        } finally {
            $this->fluentAvoidParentSetAttribute = false;
        }

        return $this;
    }

    /**
     * Overload the method to populate public properties from Model attributes
     * Set a given attribute on the model.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if ($this->fluentAvoidParentSetAttribute) {
            return $this;
        }

        // Tricky part to prevent attribute overwriting by mergeAttributesFromClassCasts
        if ($this->hasFluentProperty($key)) {
            unset($this->{$key});
        }

        parent::setAttribute($key, $value);

        if ($this->hasFluentProperty($key)) {
            $this->{$key} = $this->getAttribute($key);
        }

        return $this;
    }

    /**
     * Overload the method to populate attributes from public properties
     * Merge the cast class attributes back into the model.
     *
     * @return void
     */
    public function mergeAttributesFromClassCasts()
    {
        $this->getFluentProperties()
            ->filter(function (ReflectionProperty $property) {
                return $property->isInitialized($this);
            })
            ->each(function (ReflectionProperty $property) {
                parent::setAttribute($property->getName(), $this->{$property->getName()});
            });

        parent::mergeAttributesFromClassCasts();
    }

    /**
     * Hydrate public properties on model retrieve.
     *
     * @return void
     */
    protected static function bootHasProperties()
    {
        self::retrieved(function (self $model) {
            $model->hydrateFluentProperties();
        });
    }

    /**
     * Determine if a model has a public property.
     *
     * @param  string  $key
     * @return bool
     */
    protected function hasFluentProperty(string $key): bool
    {
        return $this->getFluentProperties()
            ->contains(fn (ReflectionProperty $property) => $property->getName() === $key);
    }

    /**
     * Hydrate public properties with attributes data.
     *
     * @return void
     */
    public function hydrateFluentProperties(): void
    {
        $this->getFluentProperties()
            ->filter(fn (ReflectionProperty $property) => array_key_exists($property->getName(), $this->attributes))
            ->each(function (ReflectionProperty $property) {
                $value = $this->getAttribute($property->getName());

                if (is_null($value) && ! $property->getType()->allowsNull()) {
                    return;
                }

                $this->{$property->getName()} = $value;
            });
    }

    protected function buildFluentDefaults(): void
    {
        $propertyDefinedDefaults = [];

        $this->getFluentProperties()
            ->filter(fn (ReflectionProperty $property) => $property->hasDefaultValue())
            ->each(function (ReflectionProperty $property) use (&$propertyDefinedDefaults) {
                $propertyDefinedDefaults[$property->getName()] = $property->getDefaultValue();
            });

        $this->attributes = array_merge($this->attributes, $propertyDefinedDefaults);
    }

    /**
     * Build model casts for public properties.
     *
     * @return void
     */
    protected function buildFluentCasts(): void
    {
        $nativeCasts = $this->getFluentProperties()
            ->reject(function (ReflectionProperty $property) {
                return in_array(
                    $property->getName(),
                    [
                        static::CREATED_AT,
                        static::UPDATED_AT,
                        defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at',
                    ]
                );
            })
            ->mapWithKeys(function (ReflectionProperty $property) {
                return [$property->getName() => $this->getFluentCastType($property)];
            })
            ->whereNotNull()
            ->toArray();

        $this->casts = array_merge($this->casts, $nativeCasts);
    }

    /**
     * Get cast type from native property type.
     *
     * @param  \ReflectionProperty  $property
     * @return null|string
     */
    protected function getFluentCastType(ReflectionProperty $property): ?string
    {
        $type = str_replace('?', '', $property->getType());

        if ($attribute = $property->getAttributes()[0] ?? null) {
            return $this->castFluentAttribute($attribute) ?? $type;
        }

        return match ($type) {
            Collection::class => 'collection',
            Carbon::class => 'datetime',
            'bool' => 'boolean',
            'int' => 'integer',
            default => $type,
        };
    }

    /**
     * Get cast type defined by an attribute.
     *
     * @param  \ReflectionAttribute  $attribute
     * @return null|string
     */
    protected function castFluentAttribute(ReflectionAttribute $attribute): ?string
    {
        if ($attribute->getName() === Cast::class) {
            return $attribute->getArguments()[0];
        }

        if (is_subclass_of($attribute->getName(), AbstractCaster::class)) {
            $caster = new ($attribute->getName())($attribute->getArguments()[0] ?? null);

            return collect([
                $caster->name,
                $caster->modifier ?? null,
            ])
                ->whereNotNull()
                ->join(':');
        }

        return null;
    }
}
