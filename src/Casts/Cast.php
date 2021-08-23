<?php

namespace Based\Fluent\Casts;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class Cast
{
    public function __construct(
        public string $type
    ) {
    }
}
