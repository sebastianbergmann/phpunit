<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class FlakyPropertyTest extends TestCase
{
    private static int $numberOfExamples = 0;

    #[Property(examples: 10)]
    public function testFailsOnlyForTheThirdExample(int $value): void
    {
        self::$numberOfExamples++;

        $this->assertNotSame(3, self::$numberOfExamples, 'the third example fails');
    }
}
