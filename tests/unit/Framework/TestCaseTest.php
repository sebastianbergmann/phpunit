<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function sprintf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\ExcludeGlobalVariableFromBackup;
use PHPUnit\TestFixture\TestWithDifferentNames;

#[CoversClass(TestCase::class)]
#[ExcludeGlobalVariableFromBackup('i')]
#[ExcludeGlobalVariableFromBackup('singleton')]
class TestCaseTest extends TestCase
{
    protected static int $testStatic = 456;

    public static function setUpBeforeClass(): void
    {
        $GLOBALS['a']  = 'a';
        $_ENV['b']     = 'b';
        $_POST['c']    = 'c';
        $_GET['d']     = 'd';
        $_COOKIE['e']  = 'e';
        $_SERVER['f']  = 'f';
        $_FILES['g']   = 'g';
        $_REQUEST['h'] = 'h';
        $GLOBALS['i']  = 'i';
    }

    public static function tearDownAfterClass(): void
    {
        unset(
            $GLOBALS['a'],
            $_ENV['b'],
            $_POST['c'],
            $_GET['d'],
            $_COOKIE['e'],
            $_SERVER['f'],
            $_FILES['g'],
            $_REQUEST['h'],
            $GLOBALS['i'],
        );
    }

    public function testCaseToString(): void
    {
        $this->assertEquals(
            sprintf(
                '%s::testCaseToString',
                self::class,
            ),
            $this->toString(),
        );
    }

    public function testCaseDefaultExecutionOrderDependencies(): void
    {
        $this->assertInstanceOf(Reorderable::class, $this);

        $this->assertEquals(
            [new ExecutionOrderDependency(static::class, 'testCaseDefaultExecutionOrderDependencies')],
            $this->provides(),
        );

        $this->assertEquals(
            [],
            $this->requires(),
        );
    }

    public function testGetNameReturnsMethodName(): void
    {
        $methodName = 'testWithName';

        $testCase = new TestWithDifferentNames($methodName);

        $this->assertSame($methodName, $testCase->nameWithDataSet());
    }
}
