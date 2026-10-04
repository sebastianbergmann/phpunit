<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData\WatchedFiles;

use PHPUnit\Framework\TestCase;

final class GreeterTest extends TestCase
{
    public function testGreets(): void
    {
        $configuration = require __DIR__ . '/../config/app.php';

        $this->assertSame($configuration['greeting'], (new Greeter)->greet(Helper::name()));
    }
}
