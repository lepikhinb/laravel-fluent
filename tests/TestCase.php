<?php

namespace Based\Fluent\Tests;

use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Based\Fluent\FluentServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FluentServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        Schema::create('fluent_models', function (Blueprint $table) {
            $table->id();
            $table->string('string')->nullable();
            $table->integer('integer')->nullable();
            $table->float('float')->nullable();
            $table->json('object')->nullable();
            $table->json('array')->nullable();
            $table->json('collection')->nullable();
            $table->timestamp('carbon')->nullable();
            $table->boolean('boolean')->nullable();
            $table->decimal('decimal')->nullable();
            $table->decimal('as_decimal')->nullable();
            $table->timestamp('as_date')->nullable();
            $table->string('withoutCast')->nullable();
            $table->json('nullable_array')->nullable();
            $table->string('encrypted')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->string('name');
            $table->timestamps();
        });
    }
}
