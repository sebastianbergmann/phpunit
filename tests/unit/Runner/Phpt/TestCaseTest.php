<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

use function realpath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

#[CoversClass(TestCase::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/phpt')]
final class TestCaseTest extends FrameworkTestCase
{
    public function testSortIdReturnsFilename(): void
    {
        $filename = realpath(__DIR__ . '/../../../_files/success.phpt');
        $testCase = new TestCase($filename);

        $this->assertSame($filename, $testCase->sortId());
    }

    public function testCountReturnsOne(): void
    {
        $filename = realpath(__DIR__ . '/../../../_files/success.phpt');
        $testCase = new TestCase($filename);

        $this->assertSame(1, $testCase->count());
    }
}
