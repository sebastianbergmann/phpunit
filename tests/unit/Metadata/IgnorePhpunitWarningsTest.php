<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(IgnorePhpunitWarnings::class)]
#[Small]
final class IgnorePhpunitWarningsTest extends TestCase
{
    public static function shouldIgnoreProvider(): array
    {
        return [
            'null pattern ignores any message'         => [null, 'any warning message', true],
            'pattern matches message'                  => ['warning.*message', 'warning test message', true],
            'pattern does not match different message' => ['warning.*message', 'different message', false],
            'exact match works'                        => ['exact warning message', 'exact warning message', true],
            'exact match does not match different'     => ['exact warning message', 'different warning message', false],
        ];
    }

    #[DataProvider('shouldIgnoreProvider')]
    public function testShouldIgnore(?string $messagePattern, string $message, bool $expected): void
    {
        $metadata = Metadata::ignorePhpunitWarnings($messagePattern);

        $this->assertSame($expected, $metadata->shouldIgnore($message));
    }
}
