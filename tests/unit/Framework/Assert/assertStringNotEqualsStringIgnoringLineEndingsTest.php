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
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertStringNotEqualsStringIgnoringLineEndings')]
#[CoversMethod(Assert::class, 'stringEqualsStringIgnoringLineEndings')]
#[TestDox('assertStringNotEqualsStringIgnoringLineEndings()')]
#[Small]
#[Group('framework')]
#[Group('framework/assertions')]
final class assertStringNotEqualsStringIgnoringLineEndingsTest extends TestCase
{
    #[DataProviderExternal(assertStringEqualsStringIgnoringLineEndingsTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $expected, string $actual): void
    {
        $this->assertStringNotEqualsStringIgnoringLineEndings($expected, $actual);
    }

    #[DataProviderExternal(assertStringEqualsStringIgnoringLineEndingsTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $expected, string $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotEqualsStringIgnoringLineEndings($expected, $actual);
    }
}
