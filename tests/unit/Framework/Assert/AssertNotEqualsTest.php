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

use Exception;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\LogicalNot;

class AssertNotEqualsTest extends TestCase
{
    public function testConfusingMessagesForLogicalNot(): void
    {
        $expectedMessage = "Failed asserting that 'test contains something' is not equal to 'test contains something'.";
        $a               = 'test contains something';
        $b               = 'test contains something';
        $constraint      = new LogicalNot(new IsEqual($a));

        try {
            Assert::assertThat($b, $constraint);
        } catch (Exception $e) {
            $actualMessage = $e->getMessage();
            $this->assertSame($expectedMessage, $actualMessage);
        }
    }
}
