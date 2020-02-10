<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

final class Issue4037ATest extends TestCase
{
    public function testA(): void
    {
        require __DIR__ . '/Issue4037BTest.php';
        $this->assertTrue(Issue4037BTest::ok());
    }
}
