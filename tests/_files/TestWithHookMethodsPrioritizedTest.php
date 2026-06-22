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

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\TestCase;

final class TestWithHookMethodsPrioritizedTest extends TestCase
{
    #[BeforeClass(priority: 1)]
    public static function beforeFirstTest(): void
    {
    }

    #[BeforeClass(priority: -1)]
    public static function beforeFirstTestWithNegativePriority(): void
    {
    }

    #[AfterClass(priority: 6)]
    public static function afterLastTest(): void
    {
    }

    #[AfterClass(priority: -6)]
    public static function afterLastTestWithNegativePriority(): void
    {
    }

    #[Before(priority: 2)]
    protected function beforeEachTest(): void
    {
    }

    #[Before(priority: -2)]
    protected function beforeEachTestWithNegativePriority(): void
    {
    }

    #[PreCondition(priority: 3)]
    protected function preConditions(): void
    {
    }

    #[PreCondition(priority: -3)]
    protected function preConditionsWithNegativePriority(): void
    {
    }

    #[PostCondition(priority: 4)]
    protected function postConditions(): void
    {
    }

    #[PostCondition(priority: -4)]
    protected function postConditionsWithNegativePriority(): void
    {
    }

    #[After(priority: 5)]
    protected function afterEachTest(): void
    {
    }

    #[After(priority: -5)]
    protected function afterEachTestWithNegativePriority(): void
    {
    }
}
