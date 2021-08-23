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

    public function getFluentProperties(): Collection
    {
        if (isset($this->fluentProperties)) {
            return $this->fluentProperties;
        }

        $reflection = new ReflectionClass($this);

        return $this->fluentProperties = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
            ->filter(fn (ReflectionProperty $property) => $property->class === self::class);
    }

    public function mergeAttributesFromClassCasts()
    {
        $this->getFluentProperties()
            ->filter(function (ReflectionProperty $property) {
                return $property->isInitialized($this);
            })
            ->each(function (ReflectionProperty $property) {
                $this->setAttribute($property->getName(), $this->{$property->getName()});
            });

        parent::mergeAttributesFromClassCasts();
    }

    protected static function bootFluent()
    {
        self::retrieved(function (self $model) {
            $model->getFluentProperties()
                ->filter(fn (ReflectionProperty $property) => $model->getAttribute($property->getName()))
                ->each(function (ReflectionProperty $property) use ($model) {
                    $model->{$property->getName()} = $model->getAttribute($property->getName());
                });
        });
    }

    protected function hydrateFluentProperties(): void
    {
        $this->getFluentProperties()
            ->filter(function (ReflectionProperty $property) {
                return in_array($property->getName(), array_keys($this->getAttributes()));
            })
            ->each(function (ReflectionProperty $property) {
                $this->{$property->getName()} = $this->getAttribute($property->getName());
            });
    }

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

    protected function castFluentAttribute(ReflectionAttribute $attribute): ?string
    {
        if ($attribute->getName() === Cast::class) {
            return $attribute->getArguments()[0];
        }

        if (is_subclass_of($attribute->getName(), AbstractCaster::class)) {
            $caster = new ($attribute->getName())($attribute->getArguments()[0] ?? null);

            return collect([
                $caster->name,
                $caster->modifier ?? null
            ])
                ->whereNotNull()
                ->join(':');
        }

        return null;
    }
}
