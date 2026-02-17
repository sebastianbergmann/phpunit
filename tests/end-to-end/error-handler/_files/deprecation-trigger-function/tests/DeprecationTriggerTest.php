<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerFunction;

use PHPUnit\Framework\TestCase;

final class DeprecationTriggerTest extends TestCase
{
    public function testDeprecationViaFunction(): void
    {
        (new FirstPartyClass)->method();

        $this->assertTrue(true);
    }
}
