<?php

namespace Based\Fluent\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class HasMany extends AbstractRelation
{
    public function __construct(
        public string $related,
        public ?string $foreignKey = null,
        public ?string $localKey = null,
    ) {
    }
}
