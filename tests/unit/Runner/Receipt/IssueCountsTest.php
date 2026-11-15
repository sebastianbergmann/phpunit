<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Receipt;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(IssueCounts::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/receipt')]
final class IssueCountsTest extends TestCase
{
    public function testHasNumberOfErrors(): void
    {
        $this->assertSame(1, $this->issueCounts()->errors());
    }

    public function testHasNumberOfDeprecations(): void
    {
        $this->assertSame(2, $this->issueCounts()->deprecations());
    }

    public function testHasNumberOfNotices(): void
    {
        $this->assertSame(3, $this->issueCounts()->notices());
    }

    public function testHasNumberOfWarnings(): void
    {
        $this->assertSame(4, $this->issueCounts()->warnings());
    }

    public function testHasNumberOfPhpunitErrors(): void
    {
        $this->assertSame(5, $this->issueCounts()->phpunitErrors());
    }

    public function testHasNumberOfPhpunitDeprecations(): void
    {
        $this->assertSame(6, $this->issueCounts()->phpunitDeprecations());
    }

    public function testHasNumberOfPhpunitNotices(): void
    {
        $this->assertSame(7, $this->issueCounts()->phpunitNotices());
    }

    public function testHasNumberOfPhpunitWarnings(): void
    {
        $this->assertSame(8, $this->issueCounts()->phpunitWarnings());
    }

    private function issueCounts(): IssueCounts
    {
        return new IssueCounts(1, 2, 3, 4, 5, 6, 7, 8);
    }
}
