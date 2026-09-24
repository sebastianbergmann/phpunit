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

use function class_exists;
use function realpath;
use function strtolower;
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

    public function testMapsTheClassesDeclaredInLoadedTestClassFilesToTheFilesThatDeclareThem(): void
    {
        $file = realpath(__DIR__ . '/../../_files/BankAccountTest.php');

        (new TestSuiteLoader)->load($file);

        $map = TestSuiteLoader::classesDeclaredInLoadedSuiteClassFiles();

        $this->assertArrayHasKey(strtolower(BankAccountTest::class), $map);
        $this->assertSame($file, $map[strtolower(BankAccountTest::class)]);
    }

    public function testDoesNotMapAnythingForALoadedTestClassFileThatDeclaresNoClass(): void
    {
        $file = realpath(__DIR__ . '/../../_files/TestClassFileWithoutTestClass.php');

        // The file is recorded as loaded before it turns out that it declares
        // no class, so the map of the classes declared in the loaded test
        // class files has to skip it rather than trip over it.
        try {
            (new TestSuiteLoader)->load($file);
        } catch (ClassCannotBeFoundException) {
        }

        $this->assertNotContains($file, TestSuiteLoader::classesDeclaredInLoadedSuiteClassFiles());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testMapsTheClassesDeclaredInTestClassFileWhoseClassWasAutoloadedAfterAnotherTestClassFileWasLoaded(): void
    {
        $loader = new TestSuiteLoader;
        $file   = realpath(__DIR__ . '/../../_files/BankAccountTest.php');

        $loader->load(__DIR__ . '/../../_files/ActualOutputTest.php');

        $this->assertTrue(class_exists(BankAccountTest::class));

        $loader->load(__DIR__ . '/../../_files/AssertionExampleTest.php');
        $loader->load($file);

        $map = TestSuiteLoader::classesDeclaredInLoadedSuiteClassFiles();

        $this->assertArrayHasKey(strtolower(BankAccountTest::class), $map);
        $this->assertSame($file, $map[strtolower(BankAccountTest::class)]);
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
