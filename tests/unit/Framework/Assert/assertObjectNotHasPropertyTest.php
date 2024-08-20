<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;

#[CoversMethod(Assert::class, 'assertObjectNotHasProperty')]
#[TestDox('assertObjectNotHasProperty()')]
#[Small]
final class assertObjectNotHasPropertyTest extends TestCase
{
    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $object = new stdClass;

        $this->assertObjectNotHasProperty('theProperty', $object);
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $object              = new stdClass;
        $object->theProperty = 'value';

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasProperty('theProperty', $object);
    }
}
