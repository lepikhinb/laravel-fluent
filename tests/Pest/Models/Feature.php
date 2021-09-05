<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use Fluent;

    #[BelongsTo]
    public Product $product;

    protected $guarded = [];
}
