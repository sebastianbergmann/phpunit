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

use PHPUnit\Framework\TestCase;
use PHPUnit\Util\PHP\AbstractPhpProcess;

class PhptTestCaseTest extends TestCase
{
    private const EXPECT_CONTENT = <<<EOF
--TEST--
EXPECT test
--FILE--
<?php echo "Hello PHPUnit!"; ?>
--EXPECT--
Hello PHPUnit!
EOF;

    private const EXPECTF_CONTENT = <<<EOF
--TEST--
EXPECTF test
--FILE--
<?php echo "Hello PHPUnit!"; ?>
--EXPECTF--
Hello %s!
EOF;

    private const EXPECTREGEX_CONTENT = <<<EOF
--TEST--
EXPECTREGEX test
--FILE--
<?php echo "Hello PHPUnit!"; ?>
--EXPECTREGEX--
Hello [HPU]{4}[nit]{3}!
EOF;

    private const FILE_SECTION = <<<EOF
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
        $this->dirname  = \sys_get_temp_dir();
        $this->filename = $this->dirname . '/phpunit.phpt';
        \touch($this->filename);

        $this->phpProcess = $this->getMockForAbstractClass(AbstractPhpProcess::class, [], '', false);
        $this->testCase   = new PhptTestCase($this->filename, $this->phpProcess);
    }

    protected function tearDown(): void
    {
        @\unlink($this->filename);

        $this->phpProcess = null;
        $this->testCase   = null;
    }

    public function testAlwaysReportsNumberOfAssertionsIsOne(): void
    {
        $this->assertSame(1, $this->testCase->getNumAssertions());
    }

    public function testAlwaysReportsItDoesNotUseADataprovider(): void
    {
        $this->assertSame(false, $this->testCase->usesDataProvider());
    }

    public function testShouldRunFileSectionAsTest(): void
    {
        $this->setPhpContent($this->ensureCorrectEndOfLine(self::EXPECT_CONTENT));

        $fileSection = '<?php echo "Hello PHPUnit!"; ?>' . \PHP_EOL;

        $this->phpProcess
             ->expects($this->once())
             ->method('runJob')
             ->with($fileSection)
             ->will($this->returnValue(['stdout' => '', 'stderr' => '']));

        $this->testCase->run();
    }

    public function testRenderFileSection(): void
    {
        $this->setPhpContent($this->ensureCorrectEndOfLine(
            <<<EOF
--TEST--
Something to decribe it
--FILE--
<?php echo __DIR__ . __FILE__; ?>
--EXPECT--
Something
EOF
        ));

        $renderedCode = "<?php echo '" . $this->dirname . "' . '" . $this->filename . "'; ?>" . \PHP_EOL;

        $this->phpProcess
             ->expects($this->once())
             ->method('runJob')
             ->with($renderedCode)
             ->will($this->returnValue(['stdout' => '', 'stderr' => '']));

        $this->testCase->run();
    }

    public function testRenderSkipifSection(): void
    {
        $phptContent = self::EXPECT_CONTENT . \PHP_EOL;
        $phptContent .= '--SKIPIF--' . \PHP_EOL;
        $phptContent .= "<?php echo 'skip: ' . __FILE__; ?>" . \PHP_EOL;

        $this->setPhpContent($phptContent);

        $renderedCode = "<?php echo 'skip: ' . '" . $this->filename . "'; ?>" . \PHP_EOL;

        $this->phpProcess
             ->expects($this->at(0))
             ->method('runJob')
             ->with($renderedCode)
             ->will($this->returnValue(['stdout' => '', 'stderr' => '']));

        $this->testCase->run();
    }

    public function testShouldRunSkipifSectionWhenExists(): void
    {
        $skipifSection = '<?php /** Nothing **/ ?>' . \PHP_EOL;

        $phptContent = self::EXPECT_CONTENT . \PHP_EOL;
        $phptContent .= '--SKIPIF--' . \PHP_EOL;
        $phptContent .= $skipifSection;

        $this->setPhpContent($phptContent);

        $this->phpProcess
             ->expects($this->at(0))
             ->method('runJob')
             ->with($skipifSection)
             ->will($this->returnValue(['stdout' => '', 'stderr' => '']));

        $this->testCase->run();
    }

    public function testShouldNotRunTestSectionIfSkipifSectionReturnsOutputWithSkipWord(): void
    {
        $skipifSection = '<?php echo "skip: Reason"; ?>' . \PHP_EOL;

        $phptContent = self::EXPECT_CONTENT . \PHP_EOL;
        $phptContent .= '--SKIPIF--' . \PHP_EOL;
        $phptContent .= $skipifSection;

        $this->setPhpContent($phptContent);

        $this->phpProcess
             ->expects($this->once())
             ->method('runJob')
             ->with($skipifSection)
             ->will($this->returnValue(['stdout' => 'skip: Reason', 'stderr' => '']));

        $this->testCase->run();
    }

    public function testShouldRunCleanSectionWhenDefined(): void
    {
        $cleanSection = '<?php unlink("/tmp/something"); ?>' . \PHP_EOL;

        $phptContent = self::EXPECT_CONTENT . \PHP_EOL;
        $phptContent .= '--CLEAN--' . \PHP_EOL;
        $phptContent .= $cleanSection;

        $this->setPhpContent($phptContent);

        $this->phpProcess
             ->expects($this->at(1))
             ->method('runJob')
             ->with($cleanSection);

        $this->testCase->run();
    }

    public function testShouldThrowsAnExceptionWhenPhptFileIsEmpty(): void
    {
        $this->setPhpContent('');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid PHPT file');

        $this->testCase->run();
    }

    public function testShouldThrowsAnExceptionWhenFileSectionIsMissing(): void
    {
        $this->setPhpContent(
            <<<EOF
--TEST--
Something to decribe it
--EXPECT--
Something
EOF
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid PHPT file');

        $this->testCase->run();
    }

    public function testShouldThrowsAnExceptionWhenThereIsNoExpecOrExpectifOrExpecregexSectionInPhptFile(): void
    {
        $this->setPhpContent(
            <<<EOF
--TEST--
Something to decribe it
--FILE--
<?php declare(strict_types=1);
echo "Hello world!\n";
?>
EOF
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid PHPT file');

        $this->testCase->run();
    }

    public function testShouldValidateExpectSession(): void
    {
        $this->setPhpContent(self::EXPECT_CONTENT);

        $this->phpProcess
             ->expects($this->once())
             ->method('runJob')
             ->with(self::FILE_SECTION)
             ->will($this->returnValue(['stdout' => 'Hello PHPUnit!', 'stderr' => '']));

        $result = $this->testCase->run();

        $this->assertTrue($result->wasSuccessful());
    }

    public function testShouldValidateExpectfSession(): void
    {
        $this->setPhpContent(self::EXPECTF_CONTENT);

        $this->phpProcess
             ->expects($this->once())
             ->method('runJob')
             ->with(self::FILE_SECTION)
             ->will($this->returnValue(['stdout' => 'Hello PHPUnit!', 'stderr' => '']));

        $result = $this->testCase->run();

        $this->assertTrue($result->wasSuccessful());
    }

    public function testShouldValidateExpectregexSession(): void
    {
        $this->setPhpContent(self::EXPECTREGEX_CONTENT);

        $this->phpProcess
             ->expects($this->once())
             ->method('runJob')
             ->with(self::FILE_SECTION)
             ->will($this->returnValue(['stdout' => 'Hello PHPUnit!', 'stderr' => '']));

        $result = $this->testCase->run();

        $this->assertTrue($result->wasSuccessful());
    }

    /**
     * Defines the content of the current PHPT test.
     *
     * @param string $content
     */
    private function setPhpContent($content): void
    {
        \file_put_contents($this->filename, $content);
    }

    /**
     * Ensures the correct line ending is used for comparison
     *
     * @param string $content
     *
     * @return string
     */
    private function ensureCorrectEndOfLine($content)
    {
        return \strtr(
            $content,
            [
                "\r\n" => \PHP_EOL,
                "\r"   => \PHP_EOL,
                "\n"   => \PHP_EOL,
            ]
        );
    }
}
