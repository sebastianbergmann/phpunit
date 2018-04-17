<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class Issue581Test extends TestCase
{
    public function testExportingObjectsDoesNotBreakWindowsLineFeeds(): void
    {
        $this->assertEquals(
            (object) [1, 2, "Test\r\n", 4, 5, 6, 7, 8],
            (object) [1, 2, "Test\r\n", 4, 1, 6, 7, 8]
        );
    }
}
