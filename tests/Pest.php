<?php

use Orchestra\Testbench\TestCase;
use Pest\TestSuite;

uses(Based\Fluent\Tests\TestCase::class)->in('Pest');

function this(): TestCase
{
    return TestSuite::getInstance()->test;
}
