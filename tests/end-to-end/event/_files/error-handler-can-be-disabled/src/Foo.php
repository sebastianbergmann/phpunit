<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled;

use const E_USER_WARNING;
use function error_get_last;
use function fopen;
use function trigger_error;
use Exception;

final class Foo
{
    public function methodA($fileName)
    {
        $stream_handle = @fopen($fileName, 'wb');

        if ($stream_handle === false) {
            $error = error_get_last();

            throw new Exception($error['message']);
        }

        return $stream_handle;
    }

    public function methodB(): ?array
    {
        @trigger_error('Triggering', E_USER_WARNING);

        return error_get_last();
    }
}
