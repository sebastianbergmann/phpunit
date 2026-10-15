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

use function define;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(true)]
final class ConstantDefinedInBeforeClassMethodWithPreservedGlobalStateTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        define('PHPUNIT_TEST_FIXTURE_CONSTANT_DEFINED_IN_BEFORE_CLASS_METHOD', true);
    }

    public function testOne(): void
    {
        $this->assertTrue(PHPUNIT_TEST_FIXTURE_CONSTANT_DEFINED_IN_BEFORE_CLASS_METHOD);
    }
}
