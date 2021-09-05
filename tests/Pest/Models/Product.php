<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\Casts\HasMany;
use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Product extends Model
{
    use Fluent;

    public Category $category;

    #[HasMany(Feature::class)]
    public Collection $features;

    protected $guarded = [];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
