<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelDataProvider;

use function getenv;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClosureDataTest extends TestCase
{
    public static function closureProvider(): array
    {
        return [
            [static fn (): string => 'called'],
        ];
    }

    #[DataProvider('closureProvider')]
    public function testReceivesAClosure(callable $value): void
    {
        $this->assertSame('called', $value());
        $this->assertNotFalse(getenv('PHPUNIT_WORKER_ID'));
    }
}
