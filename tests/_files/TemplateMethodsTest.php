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

use PHPUnit\Framework\TestCase;
use Throwable;

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

    public function testOne(): void
    {
        print __METHOD__ . "\n";
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        print __METHOD__ . "\n";
        $this->assertTrue(false);
    }

    protected function assertPreConditions(): void
    {
        print __METHOD__ . "\n";
    }

    protected function assertPostConditions(): void
    {
        print __METHOD__ . "\n";
    }

    protected function onNotSuccessfulTest(Throwable $t): void
    {
        print __METHOD__ . "\n";

        throw $t;
    }
}
