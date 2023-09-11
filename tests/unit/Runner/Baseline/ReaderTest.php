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

#[CoversClass(Reader::class)]
#[Small]
final class ReaderTest extends TestCase
{
    public function testReadsBaselineFromFileInXmlFormat(): void
    {
        $baseline = (new Reader)->read(__DIR__ . '/../../../_files/baseline/expected.xml');

        $this->assertTrue($baseline->has($this->issue()));
        $this->assertTrue($baseline->has($this->anotherIssue()));
        $this->assertTrue($baseline->has($this->yetAnotherIssue()));
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
