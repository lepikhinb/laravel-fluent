<?php

use Based\Fluent\Tests\Models\Category;
use Based\Fluent\Tests\Models\Feature;
use Based\Fluent\Tests\Models\Mediable;
use Based\Fluent\Tests\Models\Product;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

test('has many', function () {
    /** @var Category $category */
    $category = Category::create(['name' => 'laptops']);
    $product = $category->products()->create(['name' => 'mbpro']);

    $category->load('products');

    assertInstanceOf(Product::class, $category->products->first());
    assertEquals($product->id, $category->products->first()->id);
});

test('belongs to', function () {
    /** @var Category $category */
    $category = Category::create(['name' => 'laptops']);
    /** @var Product $product */
    $product = $category->products()->create(['name' => 'mbpro']);

    $product->load('category');

    assertInstanceOf(Category::class, $product->category);
    assertEquals($category->id, $product->category->id);
});

test('eager', function () {
    /** @var Category $category */
    $category = Category::create(['name' => 'laptops']);
    /** @var Product $product */
    $product = $category->products()->create(['name' => 'mbpro']);

    $category = Category::with('products.category')->first();

    assertInstanceOf(Product::class, $category->products->first());
    assertEquals($product->id, $category->products->first()->id);

    assertInstanceOf(Category::class, $category->products->first()->category);
    assertEquals($category->id, $category->products->first()->category->id);
});

test('set relation', function () {
    /** @var Category $category */
    $category = Category::create(['name' => 'laptops']);
    /** @var Product $product */
    $product = $category->products()->create(['name' => 'mbpro']);

    $category->setRelation('products', [$product]);

    assertInstanceOf(Product::class, $category->products->first());
    assertEquals($product->id, $category->products->first()->id);
});

test('set relations', function () {
    /** @var Category $category */
    $category = Category::create(['name' => 'laptops']);
    /** @var Product $product */
    $product = $category->products()->create(['name' => 'mbpro']);

    $category->setRelations([
        'products' => [$product],
    ]);

    assertInstanceOf(Product::class, $category->products->first());
    assertEquals($product->id, $category->products->first()->id);
});

test('define hasmany with an attribute', function () {
    /** @var Category $category */
    $category = Category::create(['name' => 'laptops']);
    /** @var Product $product */
    $product = $category->products()->create(['name' => 'mbpro']);
    $product->features()->create(['name' => 'color']);

    /** @var Feature $feature */
    $feature = $product->features()->first();

    assertEquals('color', $feature->name);
});

test('define belongsto with an attribute', function () {
    /** @var Category $category */
    $category = Category::create(['name' => 'laptops']);
    /** @var Product $product */
    $product = $category->products()->create(['name' => 'mbpro']);
    /** @var Feature $feature */
    $feature = $product->features()->create(['name' => 'color']);

    /** @var Product $product2 */
    $product2 = $feature->product()->first();

    assertEquals('mbpro', $product2->name);
});

test('eagerly load relations defined with attributes', function () {
    /** @var Category $category */
    $category = Category::create(['name' => 'laptops']);
    /** @var Product $product */
    $product = $category->products()->create(['name' => 'mbpro']);
    /** @var Feature $feature */
    $feature = $product->features()->create(['name' => 'color']);

    $product = Product::with('features.product')->first();

    assertInstanceOf(Feature::class, $product->features->first());
    assertEquals($feature->id, $product->features->first()->id);

    assertInstanceOf(Product::class, $product->features->first()->product);
    assertEquals($product->id, $product->features->first()->product->id);
});

test('fluent properties can only be declared in model classes', function () {
    $mediable = Mediable::create([]);

    assertTrue($mediable->exists());
});
