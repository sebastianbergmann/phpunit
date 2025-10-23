<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RepeatWithDataProviderFailingTest extends TestCase
{
    public static function provide(): iterable
    {
        yield [true];

        yield [false];

        yield [true];
    }

    #[DataProvider('provide')]
    public function test1(bool $bool): void
    {
        $this->assertTrue($bool);
    }
}
