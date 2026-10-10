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

use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ClassWhoseDestructorThrowsOnFirstAttempt
{
    public static int $count = 0;

    public function __destruct()
    {
        self::$count++;

        if (self::$count < 2) {
            throw new RuntimeException('Error in destructor of mock object on first attempt');
        }
    }
}

final class ErrorInMockObjectDestructorTest extends TestCase
{
    #[Retry(2)]
    public function testOne(): void
    {
        $this->createMock(ClassWhoseDestructorThrowsOnFirstAttempt::class);

        $this->assertTrue(true);
    }
}
