<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\Casts\BelongsTo;
use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use Fluent;

    #[BelongsTo('product_id')]
    public Product $product;

    protected $guarded = [];
}
