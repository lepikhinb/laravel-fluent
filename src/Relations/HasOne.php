<?php

namespace Based\Fluent\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class HasOne extends OneRelation
{
    public function __construct(
        public ?string $foreignKey = null,
        public ?string $localKey = null,
    ) {
    }
}
