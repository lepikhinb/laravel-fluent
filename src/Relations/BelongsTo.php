<?php

namespace Based\Fluent\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class BelongsTo extends OneRelation
{
    public function __construct(
        public ?string $foreignKey = null,
        public ?string $ownerKey = null,
        public ?string $relation = null,
    ) {
    }
}
