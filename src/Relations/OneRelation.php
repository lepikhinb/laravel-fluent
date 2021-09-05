<?php

namespace Based\Fluent\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
abstract class OneRelation extends AbstractRelation
{
    public function __construct()
    {
    }
}
