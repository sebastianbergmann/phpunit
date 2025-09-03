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

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;

#[IgnoreDeprecations('foo')]
final class IgnoreDeprecationsWithPatternTest extends TestCase
{
    #[IgnoreDeprecations('bar')]
    public function testOne(): void
    {
        trigger_error('foo', E_USER_DEPRECATED);
        trigger_error('bar', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        trigger_error('foo', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }

    public function testThree(): void
    {
        trigger_error('foo', E_USER_DEPRECATED);
        trigger_error('baz', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }

    #[IgnoreDeprecations('bar|baz')]
    public function testFour(): void
    {
        trigger_error('foo', E_USER_DEPRECATED);
        trigger_error('bar', E_USER_DEPRECATED);
        trigger_error('baz', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }
}
