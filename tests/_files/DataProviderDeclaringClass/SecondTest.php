<?php

namespace Foo\DataProviderDeclaringClass;

use PHPUnit\Framework\TestCase;

class SecondTest extends TestCase
{
    const DUMMY = 'dummy';

    public function testSecond()
    {
        $this->assertTrue(true);
    }
}
