<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use const BAR;
use const FOO;
use const PATH_SEPARATOR;
use function getenv;
use function ini_get;
use function ini_set;
use function putenv;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\XmlConfiguration\Loader;

#[CoversClass(PhpHandler::class)]
#[Medium]
final class PhpHandlerTest extends TestCase
{
    #[BackupGlobals(true)]
    public function testPHPConfigurationIsHandledCorrectly(): void
    {
        $savedIniHighlightKeyword = ini_get('highlight.keyword');

        $this->handle();

        $path = TEST_FILES_PATH . '.' . PATH_SEPARATOR . '/path/to/lib';
        $this->assertStringStartsWith($path, ini_get('include_path'));
        $this->assertEquals('#123456', ini_get('highlight.keyword'));
        $this->assertFalse(FOO);
        $this->assertTrue(BAR);
        $this->assertFalse($GLOBALS['foo']);
        $this->assertTrue((bool) $_ENV['foo']);
        $this->assertEquals(1, getenv('foo'));
        $this->assertEquals('bar', $_POST['foo']);
        $this->assertEquals('bar', $_GET['foo']);
        $this->assertEquals('bar', $_COOKIE['foo']);
        $this->assertEquals('bar', $_SERVER['foo']);
        $this->assertEquals('bar', $_FILES['foo']);
        $this->assertEquals('bar', $_REQUEST['foo']);

        ini_set('highlight.keyword', $savedIniHighlightKeyword);
    }

    #[BackupGlobals(true)]
    #[Ticket('https://github.com/sebastianbergmann/phpunit/issues/1181')]
    public function testHandlePHPConfigurationDoesNotOverwriteExistingEnvArrayVariables(): void
    {
        $_ENV['foo'] = false;

        $this->handle();

        $this->assertFalse($_ENV['foo']);
        $this->assertEquals('forced', getenv('foo_force'));
    }

    #[BackupGlobals(true)]
    #[Ticket('https://github.com/sebastianbergmann/phpunit/issues/1181')]
    public function testHandlePHPConfigurationDoesNotOverwriteVariablesFromPutEnv(): void
    {
        $backupFoo = getenv('foo');

        putenv('foo=putenv');

        $this->handle();

        $this->assertEquals('putenv', $_ENV['foo']);
        $this->assertEquals('putenv', getenv('foo'));

        if ($backupFoo === false) {
            putenv('foo');     // delete variable from environment
        } else {
            putenv("foo={$backupFoo}");
        }
    }

    #[BackupGlobals(true)]
    #[Ticket('https://github.com/sebastianbergmann/phpunit/issues/1181')]
    public function testHandlePHPConfigurationDoesOverwriteVariablesFromPutEnvWhenForced(): void
    {
        putenv('foo_force=putenv');

        $this->handle();

        $this->assertEquals('forced', $_ENV['foo_force']);
        $this->assertEquals('forced', getenv('foo_force'));
    }

    #[BackupGlobals(true)]
    #[Ticket('https://github.com/sebastianbergmann/phpunit/issues/2353')]
    public function testHandlePHPConfigurationDoesForceOverwrittenExistingEnvArrayVariables(): void
    {
        $_ENV['foo_force'] = false;

        $this->handle();

        $this->assertEquals('forced', $_ENV['foo_force']);
        $this->assertEquals('forced', getenv('foo_force'));
    }

    private function handle(): void
    {
        $configuration = (new Loader)->load(TEST_FILES_PATH . 'configuration.xml')->php();

        (new PhpHandler)->handle($configuration);
    }
}
