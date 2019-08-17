<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @runClassInSeparateProcess
 */
class TestWithAnnotations extends TestCase
{
    public static function providerMethod()
    {
        return [[0]];
    }

    /**
     * @backupGlobals enabled
     */
    public function testThatInteractsWithGlobalVariables(): void
    {
    }

    /**
     * @backupStaticAttributes enabled
     */
    public function testThatInteractsWithStaticAttributes(): void
    {
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInSeparateProcess(): void
    {
    }

    /**
     * @backupGlobals enabled
     * @dataProvider providerMethod
     */
    public function testThatInteractsWithGlobalVariablesWithDataProvider(): void
    {
    }

    /**
     * @backupStaticAttributes enabled
     * @dataProvider providerMethod
     */
    public function testThatInteractsWithStaticAttributesWithDataProvider(): void
    {
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider providerMethod
     */
    public function testInSeparateProcessWithDataProvider(): void
    {
    }
}
