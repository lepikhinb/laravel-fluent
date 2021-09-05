# Laravel Fluent

The package provides an expressive "fluent" way to define model attributes. It automatically builds casts at the runtime and adds a native autocompletion to the models' properties.

## Introduction
With `laravel-fluent`, you can define Model attributes as you would do with any other class. The values will be transformed to the corresponding types depending on the native types of the properties.

Before:
```php
<?php

/**
 * @property Collection $features
 * @property float $price
 * @property int $available
 */
class Product extends Model
{
    protected $casts = [
        'features' => 'collection',
        'price' => 'float',
        'available' => 'integer',
    ];
}
```

After:
```php
<?php

class Product extends Model
{
    use Fluent;

    public Category $category;
    public Collection $features;
    public float $price;
    public int $available;
}
```

## Installation

This version supports PHP 8.0. You can install the package via composer:

```bash
composer require based/laravel-fluent
```

Then, add the `Based\Fluent\Fluent` trait to your models:
```php
<?php

class User extends Model
{
    use Fluent;
}
```

### Model attributes
Define the public properties. `laravel-fluent` supports all native types and Laravel primitive casts:
```php
<?php

class Order extends Model
{
    use Fluent;

    public int $amount;
    public Carbon $expires_at;

    #[AsDecimal(2)]
    public float $total;

    #[Cast('encrypted:array')]
    public array $payload;
}
```

### Relations
The package also handles relationships.

```php
<?php

class Product extends Model
{
    use Fluent;

    #[Relation]
    public Collection $features;
    public Category $category;

    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
```

Relations method declaration is still required for proper autocompletion. However, the package can automatically resolve relations from attributes.

```php
<?php

class Product extends Model
{
    use Fluent;

    #[HasMany(Feature::class)]
    public Collection $features;
    #[BelongsTo]
    public Category $category;
}
```

## Testing

```bash
composer test
```

## Todo
- [ ] Migration generator

## Credits

- [Boris Lepikhin](https://github.com/lepikhinb)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
