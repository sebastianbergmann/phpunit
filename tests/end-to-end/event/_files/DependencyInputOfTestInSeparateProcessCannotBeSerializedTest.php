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
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class DependencyInputOfTestInSeparateProcessCannotBeSerializedTest extends TestCase
{
    public function testOne(): Closure
    {
        $this->assertTrue(true);

        return static fn () => true;
    }

    #[Depends('testOne')]
    #[RunInSeparateProcess]
    public function testTwo(Closure $closure): void
    {
        $this->assertTrue($closure());
    }

    public function testThree(): void
    {
        $this->assertTrue(true);
    }
}
