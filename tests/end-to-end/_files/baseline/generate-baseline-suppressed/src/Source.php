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
use Serializable;

final class Source
{
    public function triggerDeprecation(): void
    {
        $this->deprecation();
    }

    public function triggerNotice(): void
    {
        $this->notice();
    }

    public function triggerWarning(): void
    {
        $this->warning();
    }

    public function triggerPhpDeprecation(): void
    {
        $this->phpDeprecation();
    }

    public function triggerPhpNoticeAndWarning(): void
    {
        $this->phpNoticeAndWarning();
    }

    private function deprecation(): void
    {
        @trigger_error('deprecation', E_USER_DEPRECATED);
    }

    private function notice(): void
    {
        @trigger_error('notice', E_USER_NOTICE);
    }

    private function warning(): void
    {
        @trigger_error('warning', E_USER_WARNING);
    }

    private function phpDeprecation(): void
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
    }

    private function phpNoticeAndWarning(): void
    {
        $o = new class
        {
            public static $a = 'b';
        };

        @$o->a;
    }
}
