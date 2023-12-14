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

final class TestForDeprecatedFeatureTest extends TestCase
{
    #[IgnoreDeprecations]
    public function testOne(): void
    {
        $this->expectUserDeprecationMessage('message');

        @trigger_error('message', E_USER_DEPRECATED);
    }

    #[IgnoreDeprecations]
    public function testTwo(): void
    {
        $this->expectUserDeprecationMessage('message');

        @trigger_error('something else', E_USER_DEPRECATED);
    }

    #[IgnoreDeprecations]
    public function testThree(): void
    {
        $this->expectUserDeprecationMessageMatches('/message/');

        @trigger_error('...message...', E_USER_DEPRECATED);
    }

    #[IgnoreDeprecations]
    public function testFour(): void
    {
        $this->expectUserDeprecationMessageMatches('message');

        @trigger_error('something else', E_USER_DEPRECATED);
    }
}
