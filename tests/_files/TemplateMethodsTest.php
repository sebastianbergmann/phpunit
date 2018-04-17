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
        print __METHOD__ . "\n";
    }

    public static function tearDownAfterClass(): void
    {
        print __METHOD__ . "\n";
    }

    protected function setUp(): void
    {
        print __METHOD__ . "\n";
    }

    protected function tearDown(): void
    {
        print __METHOD__ . "\n";
    }

    public function testOne()
    {
        print __METHOD__ . "\n";
        $this->assertTrue(true);
    }

    public function testTwo()
    {
        print __METHOD__ . "\n";
        $this->assertTrue(false);
    }

    protected function assertPreConditions()
    {
        print __METHOD__ . "\n";
    }

    protected function assertPostConditions()
    {
        print __METHOD__ . "\n";
    }

    protected function onNotSuccessfulTest(Throwable $t)
    {
        print __METHOD__ . "\n";

        throw $t;
    }
}
