<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\AttributesOnTemplateMethods;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TestAttributeOnHookMethodsTest extends TestCase
{
    #[BeforeClass]
    #[Test]
    public static function before_class(): void
    {
    }

    #[AfterClass]
    #[Test]
    public static function after_class(): void
    {
    }

    #[Test]
    public static function setUpBeforeClass(): void
    {
    }

    #[Test]
    public static function tearDownAfterClass(): void
    {
    }

    #[Test]
    public function setUp(): void
    {
    }

    #[Test]
    public function assertPreConditions(): void
    {
    }

    #[Test]
    public function assertPostConditions(): void
    {
    }

    #[Test]
    public function tearDown(): void
    {
    }

    #[Before]
    #[Test]
    public function before_method(): void
    {
    }

    #[PreCondition]
    #[Test]
    public function pre_condition(): void
    {
    }

    #[PostCondition]
    #[Test]
    public function post_condition(): void
    {
    }

    #[After]
    #[Test]
    public function after_method(): void
    {
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
