<?php

namespace Based\Fluent\Tests\Models;

use Carbon\CarbonImmutable;
use Based\Fluent\Casts\AsDecimal;
use Based\Fluent\Casts\Cast;
use Based\Fluent\Fluent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FluentModel extends Model
{
    use Fluent;

    public string $string;
    public int $integer;
    public float $float;
    public object $object;
    public array $array;
    public Collection $collection;
    public Carbon $carbon;
    public CarbonImmutable $carbon_immutable;
    public bool $boolean;
    public ?array $nullable_array;

    #[Cast('decimal:2')]
    public float $decimal;

    #[AsDecimal(3)]
    public float $as_decimal;

    #[Cast('encrypted')]
    public string $encrypted;

    protected $guarded = [];
}
