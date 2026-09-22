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

final class AnonymousClassDataTest extends TestCase
{
    public static function anonymousClassProvider(): array
    {
        return [
            [
                new class
                {
                    public function value(): string
                    {
                        return 'from an anonymous class';
                    }
                },
            ],
        ];
    }

    #[DataProvider('anonymousClassProvider')]
    public function testReceivesAnInstanceOfAnAnonymousClass(object $value): void
    {
        $this->assertSame('from an anonymous class', $value->value());
        $this->assertNotFalse(getenv('PHPUNIT_WORKER_ID'));
    }
}
