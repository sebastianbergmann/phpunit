<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Retry;

use function sprintf;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\TestCase;

final class DependsOnFailingRetriedTest extends TestCase
{
    private static int $count = 0;

    #[Retry(2)]
    public function testOne(): void
    {
        self::$count++;

        $this->fail(sprintf('Failure on attempt %d', self::$count));
    }

    #[Depends('testOne')]
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
