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
use PHPUnit\Framework\TestCase;

class TestIssue4347 extends TestCase
{
    public static function thisMethodDataProvider()
    {
        return [
            [new Exception('my message')],
        ];
    }

    #[DataProvider('thisMethodDataProvider')]
    public function testThisMethod(Exception $expectedException): void
    {
        $this->assertSame('my message', $expectedException->getMessage());
    }
}
