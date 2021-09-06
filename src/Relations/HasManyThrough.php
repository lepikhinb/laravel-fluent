<?php

namespace Based\Fluent\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class HasManyThrough extends AbstractRelation
{
    public function __construct(
        public string $related,
        public string $through,
        public ?string $firstKey = null,
        public ?string $secondKey = null,
        public ?string $localKey = null,
        public ?string $secondLocalKey = null
    ) {
    }
}
