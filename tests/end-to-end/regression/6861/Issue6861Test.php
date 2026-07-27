<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6861;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\TestCase;

final class Issue6861Test extends TestCase
{
    #[BeforeClass]
    public static function setUpBeforeClass(): void
    {
    }

    #[AfterClass]
    public static function tearDownAfterClass(): void
    {
    }

    #[Before]
    protected function setUp(): void
    {
    }

    #[PreCondition]
    protected function assertPreConditions(): void
    {
    }

    #[PostCondition]
    protected function assertPostConditions(): void
    {
    }

    #[After]
    protected function tearDown(): void
    {
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
