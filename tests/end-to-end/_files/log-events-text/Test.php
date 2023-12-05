<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\LogEventsText;

use PHPUnit\Framework\TestCase;
use stdClass;

final class Test extends TestCase
{
    public function testExportObject(): void
    {
        $this->assertSame(new stdClass, new stdClass);
    }

    public function testExportNull(): void
    {
        $this->assertNull(null);
    }
}
