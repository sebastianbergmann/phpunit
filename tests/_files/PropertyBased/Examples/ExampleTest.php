<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Examples;

use Countable;
use DomainException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExampleTest extends TestCase
{
    /**
     * @var list<mixed>
     */
    public static array $arguments = [];

    public function testRecordsItsArguments(int $number, string $string): void
    {
        self::$arguments = [$number, $string];

        $this->assertTrue(true);
    }

    public function testFails(int $number): void
    {
        $this->fail('failure');
    }

    public function testErrors(int $number): void
    {
        throw new RuntimeException('error');
    }

    public function testDoesNotMeetTheExpectationForATestDouble(int $number): void
    {
        $this->createMock(Countable::class)->expects($this->once())->method('count');
    }

    public function testDoesNotMeetTheExpectationForItsOutput(int $number): void
    {
        $this->expectOutputString('expected');

        print 'actual';
    }

    public function testFailsWithAThrowableOfARegisteredFailureType(int $number): void
    {
        $this->registerFailureType(DomainException::class);

        throw new DomainException('registered');
    }

    public function testUsesATestDoubleWhoseDestructorThrows(int $number): void
    {
        $this->createMock(ThrowsInDestructor::class)->expects($this->never())->method('doSomething');
    }
}
