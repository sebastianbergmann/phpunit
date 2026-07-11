<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\FailOnDeprecationPrecedence;

use const E_USER_DEPRECATED;
use function trigger_error;

final class FirstParty
{
    public function triggerDeprecation(): void
    {
        trigger_error('deprecation in first-party code', E_USER_DEPRECATED);
    }
}
