<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\FilterErrorHandler;

use const E_USER_DEPRECATED;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use function trigger_error;

final class SourceClass
{
    public function doSomething(): void
    {
        trigger_error('deprecation', E_USER_DEPRECATED);
        trigger_error('notice', E_USER_NOTICE);
        trigger_error('warning', E_USER_WARNING);

        (new VendorClass)->doSomething();
    }
}
