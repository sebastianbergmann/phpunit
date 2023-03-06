<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

#[CoversClass(Cloner::class)]
#[Small]
final class ClonerTest extends TestCase
{
    public function testReturnsCloneWhenCloningWorks(): void
    {
        $object = new stdClass;

        $object->key = 'value';

        $clone = (new Cloner)->clone($object);

        $this->assertNotSame($object, $clone);
        $this->assertEquals($object, $clone);
    }

    public function testReturnsOriginalWhenCloningDoesNotWork(): void
    {
        $object = new class
        {
            public function __clone(): void
            {
                throw new RuntimeException;
            }
        };

        $this->assertSame($object, (new Cloner)->clone($object));
    }
}
