<?php

namespace Based\Fluent;

use Based\Fluent\Casts\AbstractCaster;
use Based\Fluent\Casts\Cast;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait Fluent
{
    protected Collection $fluentProperties;

    public function __construct(array $attributes = [])
    {
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
            ->filter(fn (ReflectionProperty $property) => $property->class === self::class);
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
    protected static function bootFluent()
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
