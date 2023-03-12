<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Phpt::class)]
#[CoversClass(Test::class)]
#[Small]
final class PhptTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $file = 'test.phpt';

        $test = new Phpt($file);

        $this->assertSame($file, $test->file());
        $this->assertSame($file, $test->id());
        $this->assertSame($file, $test->name());
        $this->assertTrue($test->isPhpt());
        $this->assertFalse($test->isTestMethod());
    }
}
