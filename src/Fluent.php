<?php

namespace Based\Fluent;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait Fluent
{
    use HasRelations,
        HasProperties;
}
