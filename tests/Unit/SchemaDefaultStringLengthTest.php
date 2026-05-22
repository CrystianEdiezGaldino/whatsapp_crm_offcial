<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Builder;
use ReflectionClass;
use Tests\TestCase;

class SchemaDefaultStringLengthTest extends TestCase
{
    public function test_default_string_length_is_191_for_mysql_indexes(): void
    {
        $property = (new ReflectionClass(Builder::class))->getProperty('defaultStringLength');
        $property->setAccessible(true);

        $this->assertSame(191, $property->getValue());
    }
}
