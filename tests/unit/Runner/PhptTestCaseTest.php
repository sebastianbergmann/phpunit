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

use const PHP_EOL;
use function file_put_contents;
use function sys_get_temp_dir;
use function touch;
use function unlink;
use PHPUnit\Event;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Util\PHP\AbstractPhpProcess;

/**
 * @medium
 */
final class PhptTestCaseTest extends TestCase
{
    private const EXPECT_CONTENT = <<<'EOF'
--TEST--
EXPECT test
--FILE--
<?php echo "Hello PHPUnit!"; ?>
--EXPECT--
Hello PHPUnit!
EOF;

    private const EXPECTF_CONTENT = <<<'EOF'
--TEST--
EXPECTF test
--FILE--
<?php echo "Hello PHPUnit!"; ?>
--EXPECTF--
Hello %s!
EOF;

    private const EXPECTREGEX_CONTENT = <<<'EOF'
--TEST--
EXPECTREGEX test
--FILE--
<?php echo "Hello PHPUnit!"; ?>
--EXPECTREGEX--
Hello [HPU]{4}[nit]{3}!
EOF;

    private const EXPECT_MISSING_ASSERTION_CONTENT = <<<'EOF'
--TEST--
Missing EXPECTF value test
--EXPECTF--
--FILE--
<?php echo "Hello PHPUnit!"; ?>
EOF;

    private const FILE_SECTION = <<<'EOF'
<?php echo "Hello PHPUnit!"; ?>

EOF;

    /**
     * @var string
     */
    private $dirname;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var PhptTestCase
     */
    private $testCase;

    /**
     * @var AbstractPhpProcess|\PHPUnit\Framework\MockObject\MockObject
     */
    private $phpProcess;

    protected function setUp(): void
    {
        $this->dirname  = sys_get_temp_dir();
        $this->filename = $this->dirname . '/phpunit.phpt';
        touch($this->filename);

        $this->phpProcess = $this->getMockForAbstractClass(AbstractPhpProcess::class, [], '', false);
        $this->testCase   = new PhptTestCase($this->filename, $this->phpProcess);
    }

    protected function tearDown(): void
    {
        @unlink($this->filename);

        $this->phpProcess = null;
        $this->testCase   = null;
    }

    public function testAlwaysReportsNumberOfAssertionsIsOne(): void
    {
        $this->assertSame(1, $this->testCase->numberOfAssertionsPerformed());
    }

    public function testAlwaysReportsItDoesNotUseADataprovider(): void
    {
        $this->assertSame(false, $this->testCase->usesDataProvider());
    }

    public function testShouldNotRunTestSectionIfSkipifSectionReturnsOutputWithSkipWord(): void
    {
        $skipifSection = '<?php echo "skip: Reason"; ?>' . PHP_EOL;

        $phptContent = self::EXPECT_CONTENT . PHP_EOL;
        $phptContent .= '--SKIPIF--' . PHP_EOL;
        $phptContent .= $skipifSection;

        $this->setPhpContent($phptContent);

        $this->phpProcess
             ->expects($this->once())
             ->method('runJob')
             ->with($skipifSection)
             ->willReturn(['stdout' => 'skip: Reason', 'stderr' => '']);

        $this->testCase->run(new Event\Dispatcher(), new TestResult);
    }

    public function testShouldSkipTestWhenPhptFileIsEmpty(): void
    {
        $this->setPhpContent('');

        $result = new TestResult;

        $this->testCase->run(new Event\Dispatcher(), $result);

        $this->assertCount(1, $result->skipped());
        $this->assertSame('Invalid PHPT file', $result->skipped()[0]->thrownException()->getMessage());
    }

    public function testShouldSkipTestWhenFileSectionIsMissing(): void
    {
        $this->setPhpContent(
            <<<'EOF'
--TEST--
Something to describe it
--EXPECT--
Something
EOF
        );

        $result = new TestResult;

        $this->testCase->run(new Event\Dispatcher(), $result);

        $this->assertCount(1, $result->skipped());
        $this->assertSame('Invalid PHPT file', $result->skipped()[0]->thrownException()->getMessage());
    }

    public function testShouldSkipTestWhenThereIsNoExpecOrExpectifOrExpecregexSectionInPhptFile(): void
    {
        $this->setPhpContent(
            <<<EOF
--TEST--
Something to describe it
--FILE--
<?php declare(strict_types=1);
echo "Hello world!\n";
?>
EOF
        );

        $result = new TestResult;

        $this->testCase->run(new Event\Dispatcher(), $result);

        $this->assertCount(1, $result->skipped());
        $skipMessage = $result->skipped()[0]->thrownException()->getMessage();
        $this->assertSame('Invalid PHPT file', $skipMessage);
    }

    public function testShouldSkipTestWhenSectionHeaderIsMalformed(): void
    {
        $this->setPhpContent(
            <<<'EOF'
----
--TEST--
This is not going to work out
--EXPECT--
Tears and misery
EOF
        );

        $result = new TestResult;

        $this->testCase->run(new Event\Dispatcher(), $result);

        $this->assertCount(1, $result->skipped());
        $skipMessage = $result->skipped()[0]->thrownException()->getMessage();
        $this->assertSame('Invalid PHPT file: empty section header', $skipMessage);
    }

    public function testShouldSkipTestWhenExpectHasNoValue(): void
    {
        $this->setPhpContent(self::EXPECT_MISSING_ASSERTION_CONTENT);

        $result = new TestResult;

        $this->testCase->run(new Event\Dispatcher(), $result);

        $this->assertCount(1, $result->errors());
        $skipMessage = $result->errors()[0]->thrownException()->getMessage();
        $this->assertSame('No PHPT expectation found', $skipMessage);
    }

    /**
     * @testdox PHPT tests return their filename as test name
     */
    public function testPHPTReturnsFilenameAsTestName(): void
    {
        $this->assertSame($this->filename, $this->testCase->getName());
    }

    /**
     * @testdox PHPT tests return their filename as unique sortId
     */
    public function testPHPTReturnsFilenameAsSortId(): void
    {
        $this->assertSame($this->filename, $this->testCase->sortId());
    }

    /**
     * @testdox PHPT tests do not affect dependency resolution
     */
    public function testPHPTDoesNotAffectDependencyResolution(): void
    {
        $this->assertSame([], $this->testCase->provides());
        $this->assertSame([], $this->testCase->requires());
    }

    /**
     * Defines the content of the current PHPT test.
     *
     * @param string $content
     */
    private function setPhpContent($content): void
    {
        file_put_contents($this->filename, $content);
    }
}
