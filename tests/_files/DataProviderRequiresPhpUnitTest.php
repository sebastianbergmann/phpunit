<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\TestCase;

final class DataProviderRequiresPhpUnitTest extends TestCase
{
    public static function providerThatThrows(): array
    {
        throw new Exception('Should have been skipped.');
    }

    public static function validProvider(): array
    {
        return [[true], [true]];
    }

    public function invalidProvider(): array
    {
        return [[true], [true]];
    }

    #[RequiresPhpunit('< 10')]
    #[DataProvider('invalidProvider')]
    public function testWithInvalidDataProvider(bool $param): void
    {
        $this->assertTrue($param);
    }

    #[RequiresPhpunit('>= 10')]
    #[DataProvider('validProvider')]
    public function testWithValidDataProvider(bool $param): void
    {
        $this->assertTrue($param);
    }

    #[RequiresPhpunit('< 10')]
    #[DataProvider('providerThatThrows')]
    public function testWithDataProviderThatThrows(): void
    {
    }

    #[RequiresPhpunit('< 10')]
    #[DataProviderExternal(self::class, 'providerThatThrows')]
    public function testWithDataProviderExternalThatThrows(): void
    {
    }
}
