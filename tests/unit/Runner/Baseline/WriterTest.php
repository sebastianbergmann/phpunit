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

use function sha1;
use function sys_get_temp_dir;
use function tempnam;
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
        $this->target = tempnam(sys_get_temp_dir(), sha1(__FILE__));
    }

    protected function tearDown(): void
    {
        @unlink($this->target);
    }

    public function testWritesBaselineToFileInXmlFormat(): void
    {
        (new Writer)->write($this->target, $this->baseline());

        $this->assertXmlFileEqualsXmlFile(__DIR__ . '/../../../_files/baseline.xml', $this->target);
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
