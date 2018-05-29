<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class TemplateMethodsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        print __METHOD__ . PHP_EOL;
    }

    public static function tearDownAfterClass(): void
    {
        print __METHOD__ . PHP_EOL;
    }

    protected function setUp(): void
    {
        print __METHOD__ . PHP_EOL;
    }

    protected function tearDown(): void
    {
        print __METHOD__ . PHP_EOL;
    }

    public function testOne(): void
    {
        print __METHOD__ . PHP_EOL;
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        print __METHOD__ . PHP_EOL;
        $this->assertTrue(false);
    }

    protected function assertPreConditions(): void
    {
        print __METHOD__ . PHP_EOL;
    }

    protected function assertPostConditions(): void
    {
        print __METHOD__ . PHP_EOL;
    }

    protected function onNotSuccessfulTest(Throwable $t): void
    {
        print __METHOD__ . PHP_EOL;

        throw $t;
    }
}
