<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\BankAccountTest;

#[CoversClass(TestSuiteLoader::class)]
#[Small]
final class TestSuiteLoaderTest extends TestCase
{
    public function testLoadsTestCaseClassThatIsDeclaredInFile(): void
    {
        $this->assertSame(
            BankAccountTest::class,
            (new TestSuiteLoader)->load(__DIR__ . '/../../_files/BankAccountTest.php')->getName(),
        );
    }

    public function testLoadsTestCaseClassThatIsDeclaredInFileThatHasAlreadyBeenLoaded(): void
    {
        $loader = new TestSuiteLoader;

        $loader->load(__DIR__ . '/../../_files/BankAccountTest.php');

        $this->assertSame(
            BankAccountTest::class,
            $loader->load(__DIR__ . '/../../_files/BankAccountTest.php')->getName(),
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testLoadsTestCaseClassThatWasDeclaredBeforeTheFileWasLoaded(): void
    {
        require_once __DIR__ . '/../../_files/BankAccountTest.php';

        $this->assertSame(
            BankAccountTest::class,
            (new TestSuiteLoader)->load(__DIR__ . '/../../_files/BankAccountTest.php')->getName(),
        );
    }

    public function testRejectsFileThatDeclaresClassThatDoesNotExtendTestCase(): void
    {
        $this->expectException(ClassDoesNotExtendTestCaseException::class);

        (new TestSuiteLoader)->load(__DIR__ . '/../../_files/NoTestCase.php');
    }

    public function testRejectsFileThatDeclaresAbstractTestCaseClass(): void
    {
        $this->expectException(ClassIsAbstractException::class);

        (new TestSuiteLoader)->load(__DIR__ . '/../../_files/abstract/with-test-suffix/AbstractTest.php');
    }

    public function testRejectsFileThatDoesNotDeclareAClass(): void
    {
        $this->expectException(ClassCannotBeFoundException::class);

        (new TestSuiteLoader)->load(__DIR__ . '/../../_files/FileThatDoesNotDeclareAClass.php');
    }

    public function testRejectsFileThatDoesNotExist(): void
    {
        $this->expectException(ClassCannotBeFoundException::class);

        (new TestSuiteLoader)->load(__DIR__ . '/../../_files/DoesNotExist.php');
    }
}
