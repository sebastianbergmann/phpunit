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

use function sys_get_temp_dir;
use function touch;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
}
