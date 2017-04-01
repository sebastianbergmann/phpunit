<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util\PHP;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Exception;

class AbstractPhpProcessTest extends TestCase
{
    /**
     * @var AbstractPhpProcess|\PHPUnit_Framework_MockObject_MockObject
     */
    private $phpProcess;

    protected function setUp()
    {
        $this->phpProcess = $this->getMockForAbstractClass(AbstractPhpProcess::class);
    }

    public function testShouldNotUseStderrRedirectionByDefault()
    {
        $this->assertFalse($this->phpProcess->useStderrRedirection());
    }

    public function testShouldDefinedIfUseStderrRedirection()
    {
        $this->phpProcess->setUseStderrRedirection(true);

        $this->assertTrue($this->phpProcess->useStderrRedirection());
    }

    public function testShouldDefinedIfDoNotUseStderrRedirection()
    {
        $this->phpProcess->setUseStderrRedirection(false);

        $this->assertFalse($this->phpProcess->useStderrRedirection());
    }

    public function testShouldThrowsExceptionWhenStderrRedirectionVariableIsNotABoolean()
    {
        $this->expectException(Exception::class);

        $this->phpProcess->setUseStderrRedirection(null);
    }

    public function testShouldUseGivenSettingsToCreateCommand()
    {
        $settings = [
            'allow_url_fopen=1',
            'auto_append_file=',
            'display_errors=1',
        ];

        $expectedCommandFormat  = '%s -d allow_url_fopen=1 -d auto_append_file= -d display_errors=1';
        $actualCommand          = $this->phpProcess->getCommand($settings);

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand);
    }

    public function testShouldRedirectStderrToStdoutWhenDefined()
    {
        $this->phpProcess->setUseStderrRedirection(true);

        $expectedCommandFormat  = '%s 2>&1';
        $actualCommand          = $this->phpProcess->getCommand([]);

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand);
    }

    public function testShouldUseArgsToCreateCommand()
    {
        $this->phpProcess->setArgs('foo=bar');

        $expectedCommandFormat  = '%s -- foo=bar';
        $actualCommand          = $this->phpProcess->getCommand([]);

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand);
    }

    public function testShouldHaveFileToCreateCommand()
    {
        $argumentEscapingCharacter = DIRECTORY_SEPARATOR === '\\' ? '"' : '\'';
        $expectedCommandFormat     = \sprintf('%%s -%%c %1$sfile.php%1$s', $argumentEscapingCharacter);
        $actualCommand             = $this->phpProcess->getCommand([], 'file.php');

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand);
    }

    public function testStdinGetterAndSetter()
    {
        $this->phpProcess->setStdin('foo');

        $this->assertEquals('foo', $this->phpProcess->getStdin());
    }

    public function testArgsGetterAndSetter()
    {
        $this->phpProcess->setArgs('foo=bar');

        $this->assertEquals('foo=bar', $this->phpProcess->getArgs());
    }

    public function testEnvGetterAndSetter()
    {
        $this->phpProcess->setEnv(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->phpProcess->getEnv());
    }

    public function testTimeoutGetterAndSetter()
    {
        $this->phpProcess->setTimeout(30);

        $this->assertEquals(30, $this->phpProcess->getTimeout());
    }
}
