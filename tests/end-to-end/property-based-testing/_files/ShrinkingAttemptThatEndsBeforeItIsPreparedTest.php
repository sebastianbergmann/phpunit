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
use RuntimeException;

final class ShrinkingAttemptThatEndsBeforeItIsPreparedTest extends TestCase
{
    private static int $numberOfExamples = 0;

    /**
     * The example that falsifies the property and its four replays are
     * prepared, the first shrinking attempt is not.
     */
    protected function setUp(): void
    {
        self::$numberOfExamples++;

        if (self::$numberOfExamples === 6) {
            throw new RuntimeException('The first shrinking attempt is not prepared');
        }
    }

    /**
     * @param int<0, 100> $value
     */
    #[Property]
    public function testIsFalsifiedByEveryExample(int $value): void
    {
        $this->assertGreaterThan(100, $value);
    }
}
