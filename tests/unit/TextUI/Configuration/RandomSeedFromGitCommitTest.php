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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(RandomSeedFromGitCommit::class)]
#[Small]
final class RandomSeedFromGitCommitTest extends TestCase
{
    /**
     * @return array<string, array{positive-int, non-empty-string}>
     */
    public static function commitProvider(): array
    {
        return [
            'SHA-1'                   => [302168504000718404, '43184a7474626445f5ccbcd9660fb0235130ac1f'],
            'SHA-256'                 => [1152921504606846975, 'fffffffffffffff0000000000000000000000000000000000000000000000000'],
            'hash that begins with 0' => [1, '0000000000000000000000000000000000000001'],
        ];
    }

    /**
     * @param positive-int     $expected
     * @param non-empty-string $commit
     */
    #[DataProvider('commitProvider')]
    public function testTheSeedIsTheNumberThatTheFirst15HexadecimalDigitsOfTheHashOfTheCommitMake(int $expected, string $commit): void
    {
        $this->assertSame($expected, RandomSeedFromGitCommit::fromCommit($commit));
    }
}
