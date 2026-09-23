<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ChildProcessExtension\EndToEnd;

use const FILE_APPEND;
use const LOCK_EX;
use const PHP_EOL;
use function file_put_contents;
use function getenv;

final class Log
{
    public static function write(string $message): void
    {
        file_put_contents(
            (string) getenv('PHPUNIT_CHILD_PROCESS_EXTENSION_LOG'),
            $message . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }
}
