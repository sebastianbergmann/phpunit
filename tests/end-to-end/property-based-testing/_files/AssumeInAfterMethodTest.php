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

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class AssumeInAfterMethodTest extends TestCase
{
    private int $value = 0;

    /**
     * @param int<0, 9> $value
     */
    #[Property(examples: 10)]
    public function testExamplesAreDiscardedInAnAfterMethod(int $value): void
    {
        $this->value = $value;

        $this->assertLessThan(10, $value);
    }

    #[After]
    public function discardOddValues(): void
    {
        $this->assume($this->value % 2 === 0);
    }
}
