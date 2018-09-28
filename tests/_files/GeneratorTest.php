<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Test;

use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    public function testGenerator()
    {
        $generator = static function () {
            yield 1;

            yield 2;

            yield 3;
        };

        static::assertCount(4, $generator());
    }
}
