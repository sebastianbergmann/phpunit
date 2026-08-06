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
use const PHP_EOL;
use function getenv;
use function ini_get;
use function ini_set;
use function putenv;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use ReflectionProperty;

#[CoversClass(PhpHandler::class)]
#[Medium]
#[Group('textui')]
#[Group('textui/configuration')]
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
    #[Group('regression')]
    #[Group('regression/1181')]
    public function testHandlePHPConfigurationDoesNotOverwriteExistingEnvArrayVariables(): void
    {
        $_ENV['foo'] = false;

        $this->handle();

        $this->assertFalse($_ENV['foo']);
        $this->assertEquals('forced', getenv('foo_force'));
    }

    #[BackupGlobals(true)]
    #[Group('regression')]
    #[Group('regression/1181')]
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
    #[Group('regression')]
    #[Group('regression/1181')]
    public function testHandlePHPConfigurationDoesOverwriteVariablesFromPutEnvWhenForced(): void
    {
        putenv('foo_force=putenv');

        $this->handle();

        $this->assertEquals('forced', $_ENV['foo_force']);
        $this->assertEquals('forced', getenv('foo_force'));
    }

    #[BackupGlobals(true)]
    #[Group('regression')]
    #[Group('regression/2353')]
    public function testHandlePHPConfigurationDoesForceOverwrittenExistingEnvArrayVariables(): void
    {
        $_ENV['foo_force'] = false;

        $this->handle();

        $this->assertEquals('forced', $_ENV['foo_force']);
        $this->assertEquals('forced', getenv('foo_force'));
    }

    public function testIniSettingValueIsResolvedFromDefinedConstant(): void
    {
        $savedIniHighlightKeyword = ini_get('highlight.keyword');

        $configuration = (new Loader)->load(TEST_FILES_PATH . 'configuration_ini_with_constant.xml')->php();

        (new PhpHandler)->handle($configuration);

        $this->assertSame(PHP_EOL, ini_get('highlight.keyword'));

        ini_set('highlight.keyword', $savedIniHighlightKeyword);
    }

    public function testWarningIsTriggeredWhenIniSettingCannotBeSet(): void
    {
        $savedIniMemoryLimit = ini_get('memory_limit');

        $this->handleWithThrowAwayEventFacade(
            $this->php(
                IniSettingCollection::fromArray([
                    new IniSetting('memory_limit', 'not-a-memory-limit'),
                ]),
            ),
        );

        $this->assertSame($savedIniMemoryLimit, ini_get('memory_limit'));
    }

    #[BackupGlobals(true)]
    public function testEnvironmentVariableWithValueThatIsNotScalarIsIgnored(): void
    {
        $this->handleWithThrowAwayEventFacade(
            $this->php(
                envVariables: VariableCollection::fromArray([
                    new Variable('foo_not_scalar', ['bar'], false),
                ]),
            ),
        );

        $this->assertFalse(getenv('foo_not_scalar'));
    }

    /*
     * PhpHandler emits a test runner warning when an INI setting cannot be
     * set. This must not end up in the result of the test run that exercises
     * PhpHandler, so it is emitted into a throw-away event facade that is
     * never forwarded.
     */
    private function handleWithThrowAwayEventFacade(Php $php): void
    {
        $property = new ReflectionProperty(EventFacade::class, 'instance');
        $facade   = $property->getValue();

        $property->setValue(null, new EventFacade);

        try {
            (new PhpHandler)->handle($php);
        } finally {
            $property->setValue(null, $facade);
        }
    }

    private function php(?IniSettingCollection $iniSettings = null, ?VariableCollection $envVariables = null): Php
    {
        if ($iniSettings === null) {
            $iniSettings = IniSettingCollection::fromArray([]);
        }

        if ($envVariables === null) {
            $envVariables = VariableCollection::fromArray([]);
        }

        return new Php(
            DirectoryCollection::fromArray([]),
            $iniSettings,
            ConstantCollection::fromArray([]),
            VariableCollection::fromArray([]),
            $envVariables,
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
        );
    }

    private function handle(): void
    {
        $configuration = (new Loader)->load(TEST_FILES_PATH . 'configuration.xml')->php();

        (new PhpHandler)->handle($configuration);
    }
}
