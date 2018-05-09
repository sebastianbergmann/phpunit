<?php declare(strict_types=1);
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

class AbstractPhpProcessTest extends TestCase
{
    /**
     * @var AbstractPhpProcess|\PHPUnit\Framework\MockObject\MockObject
     */
    private $phpProcess;

    protected function setUp(): void
    {
        $this->phpProcess = $this->getMockForAbstractClass(AbstractPhpProcess::class);
    }

    protected function tearDown(): void
    {
        $this->phpProcess = null;
    }

    public function testShouldNotUseStderrRedirectionByDefault(): void
    {
        $this->assertFalse($this->phpProcess->useStderrRedirection());
    }

    public function testShouldDefinedIfUseStderrRedirection(): void
    {
        $this->phpProcess->setUseStderrRedirection(true);

        $this->assertTrue($this->phpProcess->useStderrRedirection());
    }

    public function testShouldDefinedIfDoNotUseStderrRedirection(): void
    {
        $this->phpProcess->setUseStderrRedirection(false);

        $this->assertFalse($this->phpProcess->useStderrRedirection());
    }

    public function testShouldUseGivenSettingsToCreateCommand(): void
    {
        $settings = [
            'allow_url_fopen=1',
            'auto_append_file=',
            'display_errors=1',
        ];

        $expectedCommandFormat  = '%s -d %cSETTING_0%S -d %cSETTING_1%S -d %cSETTING_2%S';
        $actualCommand          = $this->phpProcess->getCommand($settings);

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand['command']);
        $this->assertEquals(
            [
                'SETTING_0' => 'allow_url_fopen=1',
                'SETTING_1' => 'auto_append_file=',
                'SETTING_2' => 'display_errors=1',
            ],
            $actualCommand['parameters']
        );
    }

    public function testShouldRedirectStderrToStdoutWhenDefined(): void
    {
        $this->phpProcess->setUseStderrRedirection(true);

        $expectedCommandFormat  = '%s 2>&1';
        $actualCommand          = $this->phpProcess->getCommand([]);

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand['command']);
    }

    public function testShouldUseArgsToCreateCommand(): void
    {
        $this->phpProcess->setArgs('foo=bar');

        $expectedCommandFormat  = '%s -- %cARGS%S';
        $actualCommand          = $this->phpProcess->getCommand([]);

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand['command']);
        $this->assertEquals('foo=bar', $actualCommand['parameters']['ARGS']);
    }

    public function testShouldHaveFileToCreateCommand(): void
    {
        $expectedCommandFormat     = '%s %cFILE%S';
        $actualCommand             = $this->phpProcess->getCommand([], 'file.php');

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand['command']);
        $this->assertEquals('file.php', $actualCommand['parameters']['FILE']);
    }

    public function testStdinGetterAndSetter(): void
    {
        $this->phpProcess->setStdin('foo');

        $this->assertEquals('foo', $this->phpProcess->getStdin());
    }

    public function testArgsGetterAndSetter(): void
    {
        $this->phpProcess->setArgs('foo=bar');

        $this->assertEquals('foo=bar', $this->phpProcess->getArgs());
    }

    public function testEnvGetterAndSetter(): void
    {
        $this->phpProcess->setEnv(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->phpProcess->getEnv());
    }

    public function testTimeoutGetterAndSetter(): void
    {
        $this->phpProcess->setTimeout(30);

        $this->assertEquals(30, $this->phpProcess->getTimeout());
    }
}
