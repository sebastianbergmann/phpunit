<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestDox;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDoxFormatter;
use PHPUnit\Framework\TestCase;

final class FormatterMethodThrowsExceptionTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string}>
     */
    public static function provider(): array
    {
        return [
            ['string'],
        ];
    }

    public static function formatter(string $value): string
    {
        throw new Exception('message');
    }

    #[DataProvider('provider')]
    #[TestDoxFormatter('formatter')]
    public function testOne(string $value): void
    {
        $this->assertTrue(true);
    }
}
