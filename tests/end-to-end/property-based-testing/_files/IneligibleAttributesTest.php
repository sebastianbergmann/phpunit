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
use PHPUnit\Framework\Attributes\Repeat;
use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class IneligibleAttributesTest extends TestCase
{
    #[Property(examples: 10)]
    #[Repeat(2)]
    public function testIsAnnotatedWithRepeat(int $value): void
    {
        $this->assertTrue(true);
    }

    #[Property(examples: 10)]
    #[Retry(2)]
    public function testIsAnnotatedWithRetry(int $value): void
    {
        $this->assertTrue(true);
    }

    #[Property(examples: 10)]
    #[RunInSeparateProcess]
    public function testIsAnnotatedWithRunInSeparateProcess(int $value): void
    {
        $this->assertTrue(true);
    }
}
