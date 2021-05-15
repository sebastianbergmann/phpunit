<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Small]
final class SmallTest extends TestCase
{
    #[BeforeClass]
    public function beforeTests(): void
    {
    }

    #[Before]
    public function beforeTest(): void
    {
    }

    #[PreCondition]
    public function preCondition(): void
    {
    }

    #[Test]
    public function one(): void
    {
    }

    #[DataProvider('provider')]
    public function testWithDataProvider(): void
    {
    }

    #[DataProviderExternal(self::class, 'provider')]
    public function testWithDataProviderExternal(): void
    {
    }

    #[PostCondition]
    public function postCondition(): void
    {
    }

    #[After]
    public function afterTest(): void
    {
    }

    #[AfterClass]
    public function afterTests(): void
    {
    }
}
