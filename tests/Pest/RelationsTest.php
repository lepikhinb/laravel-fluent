<?php

use Based\Fluent\Tests\Models\Category;
use Based\Fluent\Tests\Models\Product;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

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
