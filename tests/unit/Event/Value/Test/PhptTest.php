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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Phpt::class)]
#[CoversClass(NoDescriptionException::class)]
#[CoversClass(Test::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/value-objects')]
final class PhptTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $file        = 'test.phpt';
        $description = 'the description';

        $test = new Phpt($file, $description);

        $this->assertSame($file, $test->file());
        $this->assertSame($file, $test->id());
        $this->assertSame($file, $test->name());
        $this->assertSame($description, $test->description());
        $this->assertTrue($test->isPhpt());
        $this->assertFalse($test->isTestMethod());
    }

    public function testMayHaveDescription(): void
    {
        $test = new Phpt('test.phpt', 'the description');

        $this->assertTrue($test->hasDescription());
        $this->assertSame('the description', $test->description());
    }

    public function testMayNotHaveDescription(): void
    {
        $test = new Phpt('test.phpt', null);

        $this->assertFalse($test->hasDescription());

        $this->expectException(NoDescriptionException::class);

        $test->description();
    }
}
