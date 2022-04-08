<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Annotation;

use PHPUnit\Framework\TestCase;

/**
 * @small
 */
final class SmallTest extends TestCase
{
    /**
     * @beforeClass
     */
    public function beforeTests(): void
    {
    }

    /**
     * @before
     */
    public function beforeTest(): void
    {
    }

    /**
     * @preCondition
     */
    public function preCondition(): void
    {
    }

    /**
     * @test
     */
    public function one(): void
    {
    }

    /**
     * @dataProvider provider
     */
    public function testWithDataProvider(): void
    {
    }

    /**
     * @dataProvider \PHPUnit\TestFixture\Metadata\Annotation\SmallTest::provider
     */
    public function testWithDataProviderExternal(): void
    {
    }

    /**
     * @postCondition
     */
    public function postCondition(): void
    {
    }

    /**
     * @after
     */
    public function afterTest(): void
    {
    }

    /**
     * @afterClass
     */
    public function afterTests(): void
    {
    }

    /**
     * @small
     */
    public function testWithSmallAnnotation(): void
    {
    }
}
