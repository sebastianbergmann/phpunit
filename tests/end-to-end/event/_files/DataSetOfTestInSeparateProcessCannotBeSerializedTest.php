<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class DataSetOfTestInSeparateProcessCannotBeSerializedTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [static fn () => true],
        ];
    }

    #[DataProvider('provider')]
    #[RunInSeparateProcess]
    public function testOne(Closure $closure): void
    {
        $this->assertTrue($closure());
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
