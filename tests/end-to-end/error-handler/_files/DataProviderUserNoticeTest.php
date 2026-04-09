<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler;

use const E_USER_NOTICE;
use function trigger_error;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DataProviderUserNoticeTest extends TestCase
{
    public static function provider(): array
    {
        trigger_error('notice from data provider', E_USER_NOTICE);

        return [[true]];
    }

    #[DataProvider('provider')]
    public function testOne(bool $value): void
    {
        $this->assertTrue($value);
    }
}
