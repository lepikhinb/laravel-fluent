<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\Casts\BelongsTo;
use Based\Fluent\Casts\HasMany;
use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Product extends Model
{
    use Fluent;

    #[BelongsTo('category_id')]
    public Category $category;

    #[HasMany(Feature::class)]
    public Collection $features;

    protected $guarded = [];
}
