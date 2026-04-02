<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6433;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FirstTest extends TestCase
{
    public static function provider(): iterable
    {
        require_once __DIR__ . '/../SideEffect.php';

        return [['foo']];
    }

    #[DataProvider('provider')]
    public function testOne(string $value): void
    {
        $this->assertSame('foo', $value);
    }
}
