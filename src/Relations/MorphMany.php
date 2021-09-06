<?php

namespace Based\Fluent\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class MorphMany extends AbstractRelation
{
    public function __construct(
        public string $related,
        public string $name,
        public ?string $type = null,
        public ?string $id = null,
        public ?string $localKey = null
    ) {
    }
}
