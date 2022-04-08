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

class DependencyOnClassTest extends TestCase
{
    /**
     * Guard support for using annotations to depend on a whole successful TestSuite.
     *
     * @depends PHPUnit\TestFixture\DependencySuccessTest::class
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/3519
     */
    public function testThatDependsOnASuccessfulClass(): void
    {
        $this->assertTrue(true);
    }

    /**
     * Guard support for using annotations to depend on a whole failing TestSuite.
     *
     * @depends PHPUnit\TestFixture\DependencyFailureTest::class
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/3519
     */
    public function testThatDependsOnAFailingClass(): void
    {
        $this->assertTrue(true);
    }
}
