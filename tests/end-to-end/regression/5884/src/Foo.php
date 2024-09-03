<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5884;

use function error_get_last;
use function fopen;
use function is_array;
use function preg_match;
use Exception;

final class Foo
{
    public static function pcreHasUtf8Support()
    {
        // This regex deliberately has a compile error to demonstrate the issue.
        return (bool) @preg_match('/^.[/u', 'a');
    }

    public static function openFile($filename): void
    {
        // Silenced the PHP native warning in favour of throwing an exception.
        $download = @fopen($filename, 'wb');

        if ($download === false) {
            $error = error_get_last();

            if (!is_array($error)) {
                // Shouldn't be possible, but can happen in test situations.
                $error = ['message' => 'Failed to open stream'];
            }

            throw new Exception($error['message']);
        }
    }
}
