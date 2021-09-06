<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\Casts\AsDecimal;
use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Based\Fluent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;

class Order extends Model
{
    use Fluent;

    public string $uuid;
    public ?array $modifiers;
    public Carbon $paid_at;

    #[AsDecimal]
    public float $total;

    #[BelongsTo]
    public User $user;

    #[HasMany(Product::class)]
    public Collection $products;
}
