<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\Relation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Category extends Model
{
    use Fluent;

    #[Relation]
    public Collection $products;

    protected $guarded = [];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
