<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DeprecationCollector;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;

final class DeprecationCollectorResetTest extends TestCase
{
    #[IgnoreDeprecations]
    public function testFirstTriggersDeprecation(): void
    {
        $this->expectUserDeprecationMessage('first deprecation');

        trigger_error('first deprecation', E_USER_DEPRECATED);
    }

    #[IgnoreDeprecations]
    public function testSecondDoesNotSeeFirstDeprecation(): void
    {
        $this->expectUserDeprecationMessage('first deprecation');
    }
}
