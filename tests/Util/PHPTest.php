<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\PHP\AbstractPhpProcess;

/**
 * @author     Henrique Moody <henriquemoody@gmail.com>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 *
 * @link       http://www.phpunit.de/
 */
class PHPUnit_Util_PHPTest extends TestCase
{
    public function testShouldNotUseStderrRedirectionByDefault()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);

        $this->assertFalse($phpMock->useStderrRedirection());
    }

    public function testShouldDefinedIfUseStderrRedirection()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);
        $phpMock->setUseStderrRedirection(true);

        $this->assertTrue($phpMock->useStderrRedirection());
    }

    public function testShouldDefinedIfDoNotUseStderrRedirection()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);
        $phpMock->setUseStderrRedirection(false);

        $this->assertFalse($phpMock->useStderrRedirection());
    }

    public function testShouldThrowsExceptionWhenStderrRedirectionVariableIsNotABoolean()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);

        $this->expectException(PHPUnit\Framework\Exception::class);

        $phpMock->setUseStderrRedirection(null);
    }

    public function testShouldUseGivenSettingsToCreateCommand()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);

        $settings = [
            'allow_url_fopen=1',
            'auto_append_file=',
            'display_errors=1',
        ];

        $expectedCommandFormat  = '%s -d allow_url_fopen=1 -d auto_append_file= -d display_errors=1';
        $actualCommand          = $phpMock->getCommand($settings);

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand);
    }

    public function testShouldRedirectStderrToStdoutWhenDefined()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);
        $phpMock->setUseStderrRedirection(true);

        $expectedCommandFormat  = '%s 2>&1';
        $actualCommand          = $phpMock->getCommand([]);

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand);
    }

    public function testShouldUseArgsToCreateCommand()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);
        $phpMock->setArgs('foo=bar');

        $expectedCommandFormat  = '%s -- foo=bar';
        $actualCommand          = $phpMock->getCommand([]);

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand);
    }

    public function testShouldHaveFileToCreateCommand()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);

        $expectedCommandFormat  = '%s -%c \'file.php\'';
        $actualCommand          = $phpMock->getCommand([], 'file.php');

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand);
    }

    public function testStdinGetterAndSetter()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);
        $phpMock->setStdin('foo');

        $this->assertEquals('foo', $phpMock->getStdin());
    }

    public function testArgsGetterAndSetter()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);
        $phpMock->setArgs('foo=bar');

        $this->assertEquals('foo=bar', $phpMock->getArgs());
    }

    public function testEnvGetterAndSetter()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);
        $phpMock->setEnv(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $phpMock->getEnv());
    }

    public function testTimeoutGetterAndSetter()
    {
        $phpMock = $this->getMockForAbstractClass(AbstractPhpProcess::class);
        $phpMock->setTimeout(30);

        $this->assertEquals(30, $phpMock->getTimeout());
    }
}
