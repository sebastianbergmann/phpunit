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
use PHPUnit\TestFixture\ParallelCodeCoverage\Beta\Two;

require_once __DIR__ . '/../src/Alpha/One.php';

require_once __DIR__ . '/../src/Beta/Two.php';

final class FirstTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertSame('a', (new One)->a());
    }

    public function testTwo(): void
    {
        $this->assertSame('c', (new Two)->c());
    }
}
