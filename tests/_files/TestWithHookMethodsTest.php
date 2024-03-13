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
    public static function beforeFirstTest(): void
    {
    }

    #[AfterClass]
    public static function afterLastTest(): void
    {
    }

    #[Before]
    protected function beforeEachTest(): void
    {
    }

    #[After]
    protected function afterEachTest(): void
    {
    }

    #[PreCondition]
    protected function preConditions(): void
    {
    }

    #[PostCondition]
    protected function postConditions(): void
    {
    }
}
