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

#[CoversClass(Reader::class)]
#[Small]
final class ReaderTest extends TestCase
{
    public function testReadsBaselineFromFileInXmlFormat(): void
    {
        $baseline = (new Reader)->read(__DIR__ . '/../../../_files/baseline.xml');

        $this->assertTrue($baseline->has($this->issue()));
        $this->assertTrue($baseline->has($this->anotherIssue()));
        $this->assertTrue($baseline->has($this->yetAnotherIssue()));
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
