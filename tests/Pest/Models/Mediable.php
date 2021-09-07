<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Tests\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Model;

class Mediable extends Model
{
    use Fluent,
        HasMedia;

    protected $guarded = [];
}
