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
        $this->assertArrayHasKey('file.php', $issues);

        $lines = $issues['file.php'];

        $this->assertCount(1, $lines);
        $this->assertArrayHasKey(1, $lines);

        $line = $lines[1];

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
            'file.php',
            1,
            'hash',
            'description',
        );
    }

    private function anotherIssue(): Issue
    {
        return Issue::from(
            'file.php',
            2,
            'hash',
            'description',
        );
    }

    private function yetAnotherIssue(): Issue
    {
        return Issue::from(
            'file.php',
            1,
            'hash',
            'yet another description',
        );
    }
}
