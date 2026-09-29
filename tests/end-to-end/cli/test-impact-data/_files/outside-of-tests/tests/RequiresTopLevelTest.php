<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData\OutsideOfTests;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/TopLevel.php';

final class RequiresTopLevelTest extends TestCase
{
    public function testReadsWhatARequiredFileSetUp(): void
    {
        $this->assertSame(['loaded'], Registry::$items);
    }
}
