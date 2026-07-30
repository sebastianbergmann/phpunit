<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestRunHistory\RepetitionsAndAttempts;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RepetitionsAndAttemptsTest extends TestCase
{
    public static function provider(): array
    {
        return [
            'named' => [true],
            [false],
        ];
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(false);
    }

    #[DataProvider('provider')]
    public function testThree(bool $value): void
    {
        $this->assertTrue($value);
    }
}
