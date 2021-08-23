<?php

namespace Based\Fluent\Casts;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class AsDecimal extends AbstractCaster
{
    public string $name = 'decimal';

    public function __construct(
        public int $modifier = 2
    ) {
    }
}
