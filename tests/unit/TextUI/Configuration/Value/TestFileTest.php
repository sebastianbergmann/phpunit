<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\VersionComparisonOperator;

#[CoversClass(TestFile::class)]
#[Small]
final class TestFileTest extends TestCase
{
    public function testHasPath(): void
    {
        $this->assertSame('path', $this->fixture()->path());
    }

    public function testHasPhpVersion(): void
    {
        $this->assertSame('8.2.0', $this->fixture()->phpVersion());
    }

    public function testHasPhpVersionOperator(): void
    {
        $this->assertSame('>=', $this->fixture()->phpVersionOperator()->asString());
    }

    public function testHasGroups(): void
    {
        $this->assertSame(['group'], $this->fixture()->groups());
    }

    private function fixture(): TestFile
    {
        return new TestFile(
            'path',
            '8.2.0',
            new VersionComparisonOperator('>='),
            ['group'],
        );
    }
}
