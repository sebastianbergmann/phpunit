<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function realpath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExcludeList::class)]
#[CoversClass(InvalidDirectoryException::class)]
#[Small]
#[RunTestsInSeparateProcesses]
final class ExcludeListTest extends TestCase
{
    public function testIsInitialized(): void
    {
        $excludeList = new ExcludeList(true);

        $this->assertContains(
            realpath(__DIR__ . '/../../../src'),
            $excludeList->getExcludedDirectories(),
        );
    }

    public function testExclusionOfFileCanBeQueried(): void
    {
        $excludeList = new ExcludeList(true);

        $this->assertTrue($excludeList->isExcluded(realpath(__DIR__ . '/../../../src/Framework/TestCase.php')));
        $this->assertFalse($excludeList->isExcluded(__FILE__));
    }

    public function testCanBeDisabled(): void
    {
        $excludeList = new ExcludeList(false);

        $this->assertFalse($excludeList->isExcluded(realpath(__DIR__ . '/../../../src/Framework/TestCase.php')));
    }

    public function testAdditionalDirectoryCanBeExcluded(): void
    {
        $directory = realpath(__DIR__);

        ExcludeList::addDirectory($directory);

        $excludeList = new ExcludeList(true);

        $this->assertContains($directory, $excludeList->getExcludedDirectories());
    }

    public function testAdditionalDirectoryThatDoesNotExistCannotBeExcluded(): void
    {
        $this->expectException(InvalidDirectoryException::class);

        ExcludeList::addDirectory('/does/not/exist');
    }
}
