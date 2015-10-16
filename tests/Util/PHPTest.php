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
 * @author     Henrique Moody <henriquemoody@gmail.com>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 *
 * @link       http://www.phpunit.de/
 * @covers     PHPUnit_Util_PHP
 */
class PHPUnit_Util_PHPTest extends PHPUnit_Framework_TestCase
{
    public function testShouldNotUseStderrRedirectionByDefault()
    {
        $phpMock = $this->getMockForAbstractClass('PHPUnit_Util_PHP');

        $this->assertFalse($phpMock->useStderrRedirection());
    }

    public function testShouldDefinedIfUseStderrRedirection()
    {
        $phpMock = $this->getMockForAbstractClass('PHPUnit_Util_PHP');
        $phpMock->setUseStderrRedirection(true);

        $this->assertTrue($phpMock->useStderrRedirection());
    }

    public function testShouldDefinedIfDoNotUseStderrRedirection()
    {
        $phpMock = $this->getMockForAbstractClass('PHPUnit_Util_PHP');
        $phpMock->setUseStderrRedirection(false);

        $this->assertFalse($phpMock->useStderrRedirection());
    }

    /**
     * @expectedException PHPUnit_Framework_Exception
     * @expectedExceptionMessage Argument #1 (No Value) of PHPUnit_Util_PHP::setUseStderrRedirection() must be a boolean
     */
    public function testShouldThrowsExceptionWhenStderrRedirectionVariableIsNotABoolean()
    {
        $phpMock = $this->getMockForAbstractClass('PHPUnit_Util_PHP');
        $phpMock->setUseStderrRedirection(null);
    }

    public function testShouldUseGivenSettingsToCreateCommand()
    {
        $phpMock = $this->getMockForAbstractClass('PHPUnit_Util_PHP');

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
        $phpMock = $this->getMockForAbstractClass('PHPUnit_Util_PHP');
        $phpMock->setUseStderrRedirection(true);

        $expectedCommandFormat  = '%s 2>&1';
        $actualCommand          = $phpMock->getCommand([]);

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand);
    }
}
