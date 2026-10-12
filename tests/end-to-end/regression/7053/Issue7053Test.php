<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue7053;

use function get_loaded_extensions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class Issue7053Test extends TestCase
{
    /**
     * @return array{0: array{0: list<string>}}
     */
    public static function provider(): array
    {
        return [[get_loaded_extensions()]];
    }

    /**
     * @param list<string> $extensionsLoadedInParentProcess
     */
    #[DataProvider('provider')]
    #[RunInSeparateProcess]
    public function testExtensionsLoadedInParentProcessAreLoadedInChildProcess(array $extensionsLoadedInParentProcess): void
    {
        $this->assertEqualsCanonicalizing($extensionsLoadedInParentProcess, get_loaded_extensions());
    }
}
