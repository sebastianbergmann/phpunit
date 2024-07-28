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

use const DIRECTORY_SEPARATOR;
use function realpath;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Writer::class)]
#[Small]
final class WriterTest extends TestCase
{
    private string $target;

    protected function setUp(): void
    {
        $this->target = realpath(__DIR__ . '/../../../_files/baseline') . DIRECTORY_SEPARATOR . 'actual.xml';
    }

    protected function tearDown(): void
    {
        @unlink($this->target);
    }

    public function testWritesBaselineToFileInXmlFormat(): void
    {
        (new Writer)->write($this->target, $this->baseline());

        $this->assertXmlFileEqualsXmlFile(__DIR__ . '/../../../_files/baseline/expected.xml', $this->target);
    }

    private function baseline(): Baseline
    {
        $baseline = new Baseline;

        $baseline->add($this->issue());
        $baseline->add($this->anotherIssue());
        $baseline->add($this->yetAnotherIssue());

        return $baseline;
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
