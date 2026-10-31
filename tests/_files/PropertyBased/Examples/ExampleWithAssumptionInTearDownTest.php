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

use DomainException;
use PHPUnit\Framework\TestCase;

final class ExampleWithAssumptionInTearDownTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->assume(false);
    }

    public function testPasses(int $number): void
    {
        $this->assertTrue(true);
    }

    public function testRejectsTheExample(int $number): void
    {
        $this->assume(false);
    }

    public function testFails(int $number): void
    {
        $this->fail('failure');
    }

    public function testFailsWithAThrowableOfARegisteredFailureType(int $number): void
    {
        $this->registerFailureType(DomainException::class);

        throw new DomainException('registered');
    }
}
