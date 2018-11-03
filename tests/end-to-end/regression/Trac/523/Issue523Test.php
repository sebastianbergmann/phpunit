<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class Issue523Test extends TestCase
{
    public function testAttributeEquals(): void
    {
        $this->assertAttributeEquals('foo', 'field', new Issue523);
    }
}

class Issue523 extends ArrayIterator
{
    protected $field = 'foo';
}
