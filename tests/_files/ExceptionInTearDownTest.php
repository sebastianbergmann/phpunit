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
use PHPUnit\Framework\TestCase;

class ExceptionInTearDownTest extends TestCase
{
    public $setUp = false;

    public $assertPreConditions = false;

    public $assertPostConditions = false;

    public $tearDown = false;

    public $testSomething = false;

    protected function setUp(): void
    {
        $this->setUp = true;
    }

    protected function tearDown(): void
    {
        $this->tearDown = true;

        throw new Exception('throw Exception in tearDown()');
    }

    public function testSomething(): void
    {
        $this->testSomething = true;
    }

    protected function assertPreConditions(): void
    {
        $this->assertPreConditions = true;
    }

    protected function assertPostConditions(): void
    {
        $this->assertPostConditions = true;
    }
}
