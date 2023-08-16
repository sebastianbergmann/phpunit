<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Baseline;

use function realpath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Baseline::class)]
#[Small]
final class BaselineTest extends TestCase
{
    public function testGroupsIssuesByFileAndLine(): void
    {
        $baseline = new Baseline;

        $baseline->add($this->issue());

        $issues = $baseline->groupedByFileAndLine();

        $this->assertCount(1, $issues);
        $this->assertArrayHasKey($this->issue()->file(), $issues);

        $lines = $issues[$this->issue()->file()];

        $this->assertCount(1, $lines);
        $this->assertArrayHasKey(10, $lines);

        $line = $lines[10];

        $this->assertCount(1, $line);
        $this->assertArrayHasKey(0, $line);

        $this->assertTrue($this->issue()->equals($line[0]));
    }

    public function testCanBeQueried(): void
    {
        $baseline = new Baseline;

        $baseline->add($this->issue());

        $this->assertTrue($baseline->has($this->issue()));
        $this->assertFalse($baseline->has($this->anotherIssue()));
        $this->assertFalse($baseline->has($this->yetAnotherIssue()));
    }

    private function issue(): Issue
    {
        return Issue::from(
            realpath(__DIR__ . '/../../../_files/baseline/FileWithIssues.php'),
            10,
            null,
            'Undefined variable $b',
        );
    }

    private function anotherIssue(): Issue
    {
        return Issue::from(
            realpath(__DIR__ . '/../../../_files/baseline/FileWithIssues.php'),
            11,
            null,
            'Undefined variable $c',
        );
    }

    private function yetAnotherIssue(): Issue
    {
        return Issue::from(
            realpath(__DIR__ . '/../../../_files/baseline/FileWithIssues.php'),
            10,
            null,
            'yet another issue',
        );
    }
}
