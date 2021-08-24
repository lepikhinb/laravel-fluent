<?php

use Based\Fluent\Tests\Models\FluentModel;

use function Pest\Laravel\assertDatabaseCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertTrue;

test('cast as string', function () {
    $model = new FluentModel([
        'string' => 'value',
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('string', $model->getCasts()['string']);
    assertEquals('value', $model->string);
    assertEquals($model->getAttribute('string'), $model->string);
});

test('cast as integer', function () {
    $model = new FluentModel([
        'integer' => 111,
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('integer', $model->getCasts()['integer']);
    assertEquals(111, $model->integer);
    assertEquals($model->getAttribute('integer'), $model->integer);
});

test('cast as float', function () {
    $model = new FluentModel([
        'float' => 11.22,
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('float', $model->getCasts()['float']);
    assertEquals(11.22, $model->float);
    assertEquals($model->getAttribute('float'), $model->float);
});

test('cast as object', function () {
    $model = new FluentModel([
        'object' => ['x' => 'y'],
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('object', $model->getCasts()['object']);
    assertEquals((object) ['x' => 'y'], $model->object);
    assertEquals($model->getAttribute('object'), $model->object);
});

test('cast as array', function () {
    $model = new FluentModel([
        'array' => ['x', 'y'],
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('array', $model->getCasts()['array']);
    assertEquals(['x', 'y'], $model->array);
    assertEquals($model->getAttribute('array'), $model->array);
});

test('cast as collection', function () {
    $model = new FluentModel([
        'collection' => collect(['x', 'y']),
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('collection', $model->getCasts()['collection']);
    assertEquals(collect(['x', 'y']), $model->collection);
    assertEquals($model->getAttribute('collection'), $model->collection);
});

test('cast as datetime (carbon)', function () {
    $date = now()->startOfDay();

    $model = new FluentModel([
        'carbon' => $date,
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('datetime', $model->getCasts()['carbon']);
    assertEquals($date, $model->carbon);
    assertEquals($model->getAttribute('carbon'), $model->carbon);
});

test('cast as boolean', function () {
    $model = new FluentModel([
        'boolean' => true,
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('boolean', $model->getCasts()['boolean']);
    assertTrue($model->boolean);
    assertEquals($model->getAttribute('boolean'), $model->boolean);
});

test('cast as decimal', function () {
    $model = new FluentModel([
        'decimal' => 10.234,
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('decimal:2', $model->getCasts()['decimal']);
    assertEquals(10.23, $model->decimal);
    assertEquals($model->getAttribute('decimal'), $model->decimal);
});

test('cast as decimal with attribute caster', function () {
    $model = new FluentModel([
        'as_decimal' => 10.234,
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('decimal:3', $model->getCasts()['as_decimal']);
    assertEquals(10.234, $model->as_decimal);
    assertEquals($model->getAttribute('as_decimal'), $model->as_decimal);
});

test('set null to nullable property', function () {
    $model = new FluentModel([
        'nullable_array' => null,
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('array', $model->getCasts()['nullable_array']);
    assertEquals(null, $model->nullable_array);
    assertEquals($model->getAttribute('nullable_array'), $model->nullable_array);
});

test('cast as encrypted', function () {
    $model = new FluentModel([
        'encrypted' => 'value',
    ]);

    $model->save();

    assertDatabaseCount('fluent_models', 1);
    assertEquals('encrypted', $model->getCasts()['encrypted']);
    assertEquals('value', $model->encrypted);
    assertNotEquals('value', $model->getRawOriginal('encrypted'));
    assertEquals($model->getAttribute('encrypted'), $model->encrypted);
});
