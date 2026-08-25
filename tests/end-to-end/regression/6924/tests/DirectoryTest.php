<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6924;

use PHPUnit\Framework\Attributes\CoversDirectory;
use PHPUnit\Framework\TestCase;

#[CoversDirectory(__DIR__ . '/../src')]
final class DirectoryTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
