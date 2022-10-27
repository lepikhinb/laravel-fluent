<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\Casts\AsDecimal;
use Based\Fluent\Casts\Cast;
use Based\Fluent\Fluent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FluentModelWithDefaults extends Model
{
    public const ALPHA_DEFAULT = 123;
    public const BETA_DEFAULT = 456;

    use Fluent;

    protected $attributes = [
        'alpha' => self::ALPHA_DEFAULT
    ];

    public int $alpha;
    public int $beta = self::BETA_DEFAULT;
    public ?int $gamma;
}
