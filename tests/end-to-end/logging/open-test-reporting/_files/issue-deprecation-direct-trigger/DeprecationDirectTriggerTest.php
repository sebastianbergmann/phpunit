<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\OpenTestReporting;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/helper.php';

final class DeprecationDirectTriggerTest extends TestCase
{
    public function testOne(): void
    {
        phpunit_otr_direct_trigger_helper();

        $this->assertTrue(true);
    }
}
