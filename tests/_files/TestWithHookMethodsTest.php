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

final class TestWithHookMethodsTest extends TestCase
{
    #[BeforeClass]
    public static function beforeFirstTestWithAttribute(): void
    {
    }

    #[AfterClass]
    public static function afterLastTestWithAttribute(): void
    {
    }

    /**
     * @beforeClass
     */
    public static function beforeFirstTestWithAnnotation(): void
    {
    }

    /**
     * @afterClass
     */
    public static function afterLastTestWithAnnotation(): void
    {
    }

    #[Before]
    protected function beforeEachTestWithAttribute(): void
    {
    }

    #[After]
    protected function afterEachTestWithAttribute(): void
    {
    }

    #[PreCondition]
    protected function preConditionsWithAttribute(): void
    {
    }

    #[PostCondition]
    protected function postConditionsWithAttribute(): void
    {
    }

    /**
     * @before
     */
    protected function beforeEachTestWithAnnotation(): void
    {
    }

    /**
     * @after
     */
    protected function afterEachTestWithAnnotation(): void
    {
    }

    /**
     * @preCondition
     */
    protected function preConditionsWithAnnotation(): void
    {
    }

    /**
     * @postCondition
     */
    protected function postConditionsWithAnnotation(): void
    {
    }
}
