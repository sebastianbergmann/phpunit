<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Extension;

use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ChildProcessExtension\ChildProcessAwareExtension;
use PHPUnit\TestFixture\ChildProcessExtension\ChildProcessUnawareExtension;
use PHPUnit\TestFixture\ChildProcessExtension\FailingChildProcessExtension;
use PHPUnit\TestFixture\ChildProcessExtension\PreparedSubscriber;
use PHPUnit\TestFixture\ChildProcessExtension\ShutdownFailingChildProcessExtension;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;

#[CoversClass(ChildProcessExtensionBootstrapper::class)]
#[UsesClass(ParameterCollection::class)]
#[Small]
final class ChildProcessExtensionBootstrapperTest extends TestCase
{
    protected function setUp(): void
    {
        ChildProcessAwareExtension::$parameters  = null;
        ChildProcessAwareExtension::$shutdowns   = 0;
        FailingChildProcessExtension::$shutdowns = 0;
    }

    public function testBootstrapsAnExtensionThatImplementsTheChildProcessInterfaceWithItsParameters(): void
    {
        $facade = $this->createMock(ChildProcessFacade::class);

        $facade
            ->expects($this->once())
            ->method('registerSubscriber')
            ->with($this->isInstanceOf(PreparedSubscriber::class))
            ->seal();

        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->seal();

        $bootstrapper = new ChildProcessExtensionBootstrapper(ConfigurationRegistry::get(), $facade, $emitter);

        $bootstrapper->bootstrap(ChildProcessAwareExtension::class, ['key' => 'value']);

        $this->assertNotNull(ChildProcessAwareExtension::$parameters);
        $this->assertSame('value', ChildProcessAwareExtension::$parameters->get('key'));
    }

    public function testSkipsAnExtensionThatDoesNotImplementTheChildProcessInterfaceWithoutComment(): void
    {
        $facade = $this->createMock(ChildProcessFacade::class);

        $facade
            ->expects($this->never())
            ->method('registerSubscriber')
            ->seal();

        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->seal();

        $bootstrapper = new ChildProcessExtensionBootstrapper(ConfigurationRegistry::get(), $facade, $emitter);

        $bootstrapper->bootstrap(ChildProcessUnawareExtension::class, []);
        $bootstrapper->shutdown();
    }

    public function testSkipsAClassThatDoesNotExistWithoutComment(): void
    {
        // The main process has warned about the class already.
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->seal();

        $bootstrapper = new ChildProcessExtensionBootstrapper(ConfigurationRegistry::get(), $this->createStub(ChildProcessFacade::class), $emitter);

        $bootstrapper->bootstrap('PHPUnit\\TestFixture\\ChildProcessExtension\\ExtensionThatDoesNotExist', []);
        $bootstrapper->shutdown();
    }

    public function testTriggersWarningWhenBootstrappingAnExtensionFailsAndDoesNotShutItDown(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->with($this->stringStartsWith('Bootstrapping of extension ' . FailingChildProcessExtension::class . ' in a separate process failed: the child process bootstrap failed'))
            ->seal();

        $bootstrapper = new ChildProcessExtensionBootstrapper(ConfigurationRegistry::get(), $this->createStub(ChildProcessFacade::class), $emitter);

        $bootstrapper->bootstrap(FailingChildProcessExtension::class, []);
        $bootstrapper->shutdown();

        $this->assertSame(0, FailingChildProcessExtension::$shutdowns);
    }

    public function testShutsDownAnExtensionThatWasBootstrappedOnce(): void
    {
        $bootstrapper = new ChildProcessExtensionBootstrapper(ConfigurationRegistry::get(), $this->createStub(ChildProcessFacade::class), $this->createStub(Emitter::class));

        $bootstrapper->bootstrap(ChildProcessAwareExtension::class, []);
        $bootstrapper->shutdown();
        $bootstrapper->shutdown();

        $this->assertSame(1, ChildProcessAwareExtension::$shutdowns);
    }

    public function testTriggersWarningWhenShuttingDownAnExtensionFailsAndShutsDownTheOthersAllTheSame(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->with($this->stringStartsWith('Shutdown of extension ' . ShutdownFailingChildProcessExtension::class . ' in a separate process failed: the child process shutdown failed'))
            ->seal();

        $bootstrapper = new ChildProcessExtensionBootstrapper(ConfigurationRegistry::get(), $this->createStub(ChildProcessFacade::class), $emitter);

        $bootstrapper->bootstrap(ShutdownFailingChildProcessExtension::class, []);
        $bootstrapper->bootstrap(ChildProcessAwareExtension::class, []);
        $bootstrapper->shutdown();

        $this->assertSame(1, ChildProcessAwareExtension::$shutdowns);
    }
}
