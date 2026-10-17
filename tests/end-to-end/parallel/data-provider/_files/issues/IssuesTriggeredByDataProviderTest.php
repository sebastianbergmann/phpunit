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

use const E_USER_DEPRECATED;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use function trigger_error;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IssuesTriggeredByDataProviderTest extends TestCase
{
    public static function provider(): array
    {
        trigger_error('deprecation in data provider', E_USER_DEPRECATED);
        trigger_error('notice in data provider', E_USER_NOTICE);
        trigger_error('warning in data provider', E_USER_WARNING);

        return [[true]];
    }

    #[DataProvider('provider')]
    public function testOne(bool $value): void
    {
        $this->assertTrue($value);
    }
}
