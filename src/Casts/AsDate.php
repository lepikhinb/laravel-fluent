<?php

namespace Based\Fluent\Casts;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class AsDate extends AbstractCaster
{
    public string $name = 'datetime';

    public function __construct(
        public ?string $modifier = null
    ) {
    }
}
