<?php

use Based\Fluent\Tests\Models\FluentModel;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

test('public attributes hydrate on model load', function () {
    FluentModel::create([
        'string' => 'value',
    ]);

    /** @var FluentModel $model */
    $model = FluentModel::first();

    assertEquals('string', $model->getCasts()['string']);
    assertEquals('value', $model->string);
    assertEquals($model->getAttribute('string'), $model->string);
    assertEquals('value', $model->toArray()['string']);
});

test('public property change affects model attributes', function () {
    $model = new FluentModel([
        'string' => 'one',
    ]);

    $model->string = 'two';

    assertEquals('two', $model->string);
    assertEquals('two', $model->getAttributes()['string']);
    assertEquals('two', $model->getAttribute('string'));
});

test('public property change affects dirty attributes array', function () {
    /** @var FluentModel $model */
    $model = FluentModel::create([
        'string' => 'one',
    ]);

    $model->string = 'two';

    assertEquals(['string' => 'two'], $model->getDirty());
});

test('public property change affects model changes array', function () {
    /** @var FluentModel $model */
    $model = FluentModel::create([
        'string' => 'one',
    ]);

    $model->string = 'two';
    $model->save();

    assertEquals(['string' => 'two'], $model->getChanges());
    assertTrue($model->wasChanged('string'));
});

test('model serializes to array with actual values', function () {
    $model = new FluentModel([
        'string' => 'one',
        'integer' => 1,
    ]);

    $model->string = 'two';
    $model->integer = 2;

    assertEquals('two', $model->toArray()['string']);
    assertEquals(2, $model->toArray()['integer']);
});
