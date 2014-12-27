<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    PHPUnit
 * @author     Henrique Moody <henriquemoody@gmail.com>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @covers     PHPUnit_Extensions_PhptTestCase
 */
class PHPUnit_Extensions_PhptTestCaseTest extends PHPUnit_Framework_TestCase
{
    const EXPECT_CONTENT = <<<EOF
--TEST--
EXPECT test
--FILE--
<?php
echo "Hello PHPUnit!";
?>
--EXPECT--
Hello PHPUnit!
EOF;
    protected $filename;
    protected $testCase;
    protected $phpUtil;

    protected function setUp()
    {
        $this->filename = sys_get_temp_dir().'/phpunit.phpt';
        file_put_contents($this->filename, self::EXPECT_CONTENT);

        $this->phpUtil = $this->getMockForAbstractClass('PHPUnit_Util_PHP', array(), '', false);

        $this->testCase = new PHPUnit_Extensions_PhptTestCase($this->filename);
        $this->testCase->setPhpUtil($this->phpUtil);
    }

    protected function tearDown()
    {
        @unlink($this->filename);

        $this->filename = null;
        $this->testCase = null;
    }

    public function testShouldRunFileSectionAsTest()
    {
        $fileSection = '<?php'.PHP_EOL.
                       'echo "Hello PHPUnit!";'.PHP_EOL.
                       '?>'.PHP_EOL;

        $this->phpUtil
            ->expects($this->once())
            ->method('runJob')
            ->with($fileSection)
            ->will($this->returnValue(array('stdout' => '', 'stderr' => '')));

        $this->testCase->run();
    }

    public function testShouldRunSkipifSectionWhenExists()
    {
        $skipifSection = '<?php /** Nothing **/ ?>'.PHP_EOL;

        $phptFileContent = self::EXPECT_CONTENT.PHP_EOL;
        $phptFileContent .= '--SKIPIF--'.PHP_EOL;
        $phptFileContent .= $skipifSection;

        file_put_contents($this->filename, $phptFileContent);

        $this->phpUtil
            ->expects($this->at(0))
            ->method('runJob')
            ->with($skipifSection)
            ->will($this->returnValue(array('stdout' => '', 'stderr' => '')));

        $this->testCase->run();
    }

    public function testShouldNotRunTestSectionIfSkipifSectionReturnsOutputWithSkipWord()
    {
        $skipifSection = '<?php echo "skip: Reason"; ?>'.PHP_EOL;

        $phptFileContent = self::EXPECT_CONTENT.PHP_EOL;
        $phptFileContent .= '--SKIPIF--'.PHP_EOL;
        $phptFileContent .= $skipifSection;

        file_put_contents($this->filename, $phptFileContent);

        $this->phpUtil
            ->expects($this->once())
            ->method('runJob')
            ->with($skipifSection)
            ->will($this->returnValue(array('stdout' => 'skip: Reason', 'stderr' => '')));

        $this->testCase->run();
    }

    public function testShouldRunCleanSectionWhenDefined()
    {
        $cleanSection = '<?php unlink("/tmp/something"); ?>' . PHP_EOL;

        $phptFileContent = self::EXPECT_CONTENT . PHP_EOL;
        $phptFileContent .= '--CLEAN--' . PHP_EOL;
        $phptFileContent .= $cleanSection;

        file_put_contents($this->filename, $phptFileContent);

        $this->phpUtil
            ->expects($this->at(1))
            ->method('runJob')
            ->with($cleanSection);

        $this->testCase->run();
    }
}
