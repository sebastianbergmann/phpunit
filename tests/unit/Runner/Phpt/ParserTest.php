<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

use function glob;
use function str_replace;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Parser::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/phpt')]
final class ParserTest extends TestCase
{
    /**
     * @return non-empty-array<non-empty-string, array{0: non-empty-string}>
     */
    public static function unsupportedSections(): array
    {
        $data = [];

        foreach (glob(__DIR__ . '/../../../_files/phpt/unsupported/*.phpt') as $file) {
            $data[str_replace([__DIR__ . '/../../../_files/phpt/unsupported/', '.phpt'], '', $file)] = [$file];
        }

        return $data;
    }

    /**
     * @return non-empty-list<array{0: non-empty-string}>
     */
    public static function invalidFiles(): array
    {
        $data = [];

        foreach (glob(__DIR__ . '/../../../_files/phpt/invalid/*.phpt') as $file) {
            $data[] = [$file];
        }

        return $data;
    }

    /**
     * @param non-empty-string $file
     */
    #[DataProvider('unsupportedSections')]
    #[TestDox('PHPT section --$_dataName-- is not supported')]
    public function testRejectsUnsupportedSections(string $file): void
    {
        $parser = new Parser;

        $this->expectException(UnsupportedPhptSectionException::class);

        $parser->parse($file);
    }

    /**
     * @param non-empty-string $file
     */
    #[DataProvider('invalidFiles')]
    public function testRejectsInvalidPhptFile(string $file): void
    {
        $parser = new Parser;

        $this->expectException(InvalidPhptFileException::class);

        $parser->parse($file);
    }
}
