<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Baseline;

use const E_USER_DEPRECATED;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use function trigger_error;
use PHPUnit\Framework\TestCase;
use Serializable;

final class Test extends TestCase
{
    public function testDeprecation(): void
    {
        @trigger_error('deprecation', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }

    public function testNotice(): void
    {
        @trigger_error('notice', E_USER_NOTICE);

        $this->assertTrue(true);
    }

    public function testWarning(): void
    {
        @trigger_error('warning', E_USER_WARNING);

        $this->assertTrue(true);
    }

    public function testPhpDeprecation(): void
    {
        @$o = new class implements Serializable
        {
            public function serialize(): void
            {
            }

            public function unserialize(string $data): void
            {
            }
        };

        $this->assertTrue(true);
    }

    public function testPhpNoticeAndWarning(): void
    {
        $o = new class
        {
            public static $a = 'b';
        };

        @$o->a;

        $this->assertTrue(true);
    }
}
