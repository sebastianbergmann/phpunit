<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\ChildProcessExitStatus;

use function sprintf;

final class ExitStatus
{
    public static function describe(?int $exitCode, ?int $signal): string
    {
        if ($signal !== null) {
            return sprintf('terminated by signal %d', $signal);
        }

        if ($exitCode !== null) {
            return sprintf('ended with exit code %d', $exitCode);
        }

        return 'ended in an unknown way';
    }
}
