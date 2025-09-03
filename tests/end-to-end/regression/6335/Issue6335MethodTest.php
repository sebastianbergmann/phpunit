<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6335;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class Issue6335MethodTest extends TestCase
{
    public static function provider(): array
    {
        return [
            1,
        ];
    }

    /**
     * @dataProvider provider
     */
    #[TestDox('$x')]
    public function testOne($x): void
    {
        $this->assertTrue(true);
    }
}
