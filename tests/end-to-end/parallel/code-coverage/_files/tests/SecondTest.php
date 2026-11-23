<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelCodeCoverage;

use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ParallelCodeCoverage\Alpha\One;

require_once __DIR__ . '/../src/Alpha/One.php';

final class SecondTest extends TestCase
{
    public function testThree(): void
    {
        $this->assertSame('b', (new One)->b());
    }
}
