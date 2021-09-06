<?php

namespace Based\Fluent\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class MorphOne extends OneRelation
{
    public function __construct(
        public string $name,
        public ?string $type = null,
        public ?string $id = null,
        public ?string $localKey = null
    ) {
    }
}
