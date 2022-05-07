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
use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Util\PHP\AbstractPhpProcess;

#[CoversClass(PhptTestCase::class)]
#[Medium]
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
    private string $filename;
    private ?PhptTestCase $testCase;
    private AbstractPhpProcess|MockObject|null $phpProcess;

    protected function setUp(): void
    {
        $this->filename = sys_get_temp_dir() . '/phpunit.phpt';

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
        $this->assertFalse($this->testCase->usesDataProvider());
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

        Facade::suspend();
        $this->testCase->run(new TestResult);
        Facade::resume();
    }

    public function testErrorIsTriggeredForEmptyPhptFile(): void
    {
        $this->setPhpContent('');

        $result = new TestResult;

        Facade::suspend();
        $this->testCase->run($result);
        Facade::resume();

        $this->assertCount(1, $result->errors());
        $this->assertSame('Invalid PHPT file', $result->errors()[0]->thrownException()->getMessage());
    }

    public function testErrorIsTriggeredWhenFileSectionIsMissing(): void
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

        Facade::suspend();
        $this->testCase->run($result);
        Facade::resume();

        $this->assertCount(1, $result->errors());
        $this->assertSame('Invalid PHPT file', $result->errors()[0]->thrownException()->getMessage());
    }

    public function testErrorIsTriggeredWhenThereIsNoExpectationSection(): void
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

        Facade::suspend();
        $this->testCase->run($result);
        Facade::resume();

        $this->assertCount(1, $result->errors());
        $this->assertSame('Invalid PHPT file', $result->errors()[0]->thrownException()->getMessage());
    }

    public function testErrorIsTriggeredWhenSectionHeaderIsMalformed(): void
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

        Facade::suspend();
        $this->testCase->run($result);
        Facade::resume();

        $this->assertCount(1, $result->errors());
        $this->assertSame('Invalid PHPT file: empty section header', $result->errors()[0]->thrownException()->getMessage());
    }

    public function testPHPTReturnsFilenameAsTestName(): void
    {
        $this->assertSame($this->filename, $this->testCase->getName());
    }

    public function testPHPTReturnsFilenameAsSortId(): void
    {
        $this->assertSame($this->filename, $this->testCase->sortId());
    }

    public function testPHPTDoesNotAffectDependencyResolution(): void
    {
        $this->assertSame([], $this->testCase->provides());
        $this->assertSame([], $this->testCase->requires());
    }

    private function setPhpContent(string $content): void
    {
        file_put_contents($this->filename, $content);
    }
}
