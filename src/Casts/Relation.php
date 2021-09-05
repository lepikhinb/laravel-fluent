<?php

namespace Based\Fluent\Casts;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Relation extends AbstractRelation
{
    public function __construct()
    {
    }
}
