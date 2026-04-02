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

use function sprintf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(GlobalState::class)]
#[CoversClass(GlobalStateResult::class)]
#[Small]
final class GlobalStateTest extends TestCase
{
    public function testIncludedFilesAsStringSkipsVfsProtocols(): void
    {
        $dir   = __DIR__;
        $files = [
            'phpunit', // The 0 index is not used
            $dir . '/GlobalStateTest.php',
            'vfs://' . $dir . '/RegexTest.php',
            'phpvfs53e46260465c7://' . $dir . '/TestClassTest.php',
            'file://' . $dir . '/XmlTest.php',
        ];

        $this->assertEquals(
            "require_once '" . $dir . "/GlobalStateTest.php';\n" .
            "require_once 'file://" . $dir . "/XmlTest.php';\n",
            GlobalState::processIncludedFilesAsString($files),
        );
    }

    public function testClosureGlobalIsSkippedAndReported(): void
    {
        $GLOBALS['__test_closure'] = static function (): string
        {
            return 'test';
        };

        try {
            $result = GlobalState::exportGlobals();

            $this->assertStringNotContainsString('__test_closure', $result->globalsString());
            $this->assertTrue($result->hasSkippedGlobals());
            $this->assertSkippedGlobal($result, '$GLOBALS[\'__test_closure\']', 'is a Closure');
        } finally {
            unset($GLOBALS['__test_closure']);
        }
    }

    public function testArrayContainingClosureIsSkippedAndReported(): void
    {
        $GLOBALS['__test_array_with_closure'] = [static fn (): int => 1];

        try {
            $result = GlobalState::exportGlobals();

            $this->assertStringNotContainsString('__test_array_with_closure', $result->globalsString());
            $this->assertTrue($result->hasSkippedGlobals());
            $this->assertSkippedGlobal($result, '$GLOBALS[\'__test_array_with_closure\']', 'is not serializable');
        } finally {
            unset($GLOBALS['__test_array_with_closure']);
        }
    }

    public function testScalarGlobalsArePreserved(): void
    {
        $GLOBALS['__test_scalar'] = 'hello';

        try {
            $result = GlobalState::exportGlobals();

            $this->assertStringContainsString('__test_scalar', $result->globalsString());
            $this->assertStringContainsString("'hello'", $result->globalsString());

            foreach ($result->skippedGlobals() as $skipped) {
                $this->assertStringNotContainsString('__test_scalar', $skipped['name']);
            }
        } finally {
            unset($GLOBALS['__test_scalar']);
        }
    }

    private function assertSkippedGlobal(GlobalStateResult $result, string $name, string $reason): void
    {
        foreach ($result->skippedGlobals() as $skipped) {
            if ($skipped['name'] === $name && $skipped['reason'] === $reason) {
                return;
            }
        }

        $this->fail(sprintf(
            'Expected skipped global with name "%s" and reason "%s"',
            $name,
            $reason,
        ));
    }
}
