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

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class DependencyAfterDependencyWithoutReturnValueTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): string
    {
        $this->assertTrue(true);

        return 'value';
    }

    public function testThree(): string
    {
        $this->fail();
    }

    #[Depends('testOne')]
    #[Depends('testTwo')]
    public function testFour(string $value): void
    {
        $this->assertSame('value', $value);
    }

    #[Depends('testOne')]
    #[Depends('testThree')]
    public function testFive(string $value): void
    {
        $this->assertSame('value', $value);
    }
}
