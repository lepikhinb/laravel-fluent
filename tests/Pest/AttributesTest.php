<?php

use Based\Fluent\Tests\Models\FluentModel;
use Based\Fluent\Tests\Models\FluentModelWithDefaults;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

test('public attributes hydrate on model load', function () {
    FluentModel::create([
        'string' => 'value',
    ]);

    /** @var FluentModel $model */
    $model = FluentModel::first();

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

test('public properties populate on fill', function () {
    $model = new FluentModel([
        'string' => 'one',
    ]);

    /** @var FluentModel $model */
    $model->fill([
        'string' => 'two',
    ]);

    assertEquals('two', $model->string);
    assertEquals($model->getAttribute('string'), $model->string);
    assertEquals('two', $model->toArray()['string']);
});

test('public properties populate on update', function () {
    /** @var FluentModel $model */
    $model = FluentModel::create([
        'string' => 'one',
    ]);

    $model->update([
        'string' => 'two',
    ]);

    assertEquals('two', $model->string);
    assertEquals($model->getAttribute('string'), $model->string);
    assertEquals('two', $model->toArray()['string']);
});

test('managed public properties receive defaults', function () {
    $model = new FluentModelWithDefaults();

    // show that we can set a default via eloquent's $attributes array
    assertEquals(FluentModelWithDefaults::ALPHA_DEFAULT, $model->alpha ?? null);
    // show that we can set a default directly on the PHP property
    assertEquals(FluentModelWithDefaults::BETA_DEFAULT, $model->beta ?? null);
    // not set with a default
    assertEquals(null, $model->gamma ?? null);
});

test('managed public property defaults do not clobber model loading', function () {
    $customAttributes = [
        'alpha' => 1,
        'beta' => 2,
        'gamma' => 3
    ];

    // this is semantically identical to how eloquent models are initialized when retrieved (i.e., from a database)
    $model = (new FluentModelWithDefaults())->newFromBuilder($customAttributes);

    foreach ($customAttributes as $k => $v) {
        /*
         * for each custom attribute we set, ensure our desired value stuck on the model,
         * and that we did NOT clobber the intended value with the default value
         */
        assertEquals($v, $model->{$k});
    }
});
