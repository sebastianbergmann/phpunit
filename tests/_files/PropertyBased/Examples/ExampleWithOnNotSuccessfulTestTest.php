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

use PHPUnit\Framework\TestCase;
use Throwable;

final class ExampleWithOnNotSuccessfulTestTest extends TestCase
{
    public static bool $onNotSuccessfulTestCalled = false;

    public function testRejectsTheExample(int $number): void
    {
        $this->assume(false);
    }

    public function testFails(int $number): void
    {
        $this->fail('failure');
    }

    public function testIsSkipped(int $number): void
    {
        $this->markTestSkipped('skipped');
    }

    protected function onNotSuccessfulTest(Throwable $t): never
    {
        self::$onNotSuccessfulTestCalled = true;

        throw $t;
    }
}
