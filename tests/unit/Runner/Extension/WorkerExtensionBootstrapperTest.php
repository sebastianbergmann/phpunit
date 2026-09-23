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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ParallelWorkerExtension\FailingWorkerExtension;
use PHPUnit\TestFixture\ParallelWorkerExtension\RecordingSubscriber;
use PHPUnit\TestFixture\ParallelWorkerExtension\ShutdownFailingWorkerExtension;
use PHPUnit\TestFixture\ParallelWorkerExtension\WorkerAwareExtension;
use PHPUnit\TestFixture\ParallelWorkerExtension\WorkerUnawareExtension;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;

#[CoversClass(WorkerExtensionBootstrapper::class)]
#[UsesClass(WorkerExtensionFacade::class)]
#[UsesClass(ParameterCollection::class)]
#[Small]
final class WorkerExtensionBootstrapperTest extends TestCase
{
    protected function setUp(): void
    {
        WorkerAwareExtension::$workerParameters = null;
        WorkerAwareExtension::$shutdowns        = 0;
    }

    public function testBootstrapsAnExtensionThatImplementsTheWorkerInterfaceWithItsParameters(): void
    {
        $facade       = new WorkerExtensionFacade;
        $bootstrapper = new WorkerExtensionBootstrapper(ConfigurationRegistry::get(), $facade);

        $bootstrapper->bootstrap(WorkerAwareExtension::class, ['key' => 'value']);

        $this->assertNotNull(WorkerAwareExtension::$workerParameters);
        $this->assertSame('value', WorkerAwareExtension::$workerParameters->get('key'));

        $this->assertCount(1, $facade->subscribers());
        $this->assertInstanceOf(RecordingSubscriber::class, $facade->subscribers()[0]);

        $this->assertSame([], $bootstrapper->warnings());
    }

    public function testSkipsAnExtensionThatDoesNotImplementTheWorkerInterfaceWithoutComment(): void
    {
        $facade       = new WorkerExtensionFacade;
        $bootstrapper = new WorkerExtensionBootstrapper(ConfigurationRegistry::get(), $facade);

        $bootstrapper->bootstrap(WorkerUnawareExtension::class, []);

        $this->assertSame([], $facade->subscribers());
        $this->assertSame([], $bootstrapper->warnings());
    }

    public function testSkipsAClassThatDoesNotExistWithoutComment(): void
    {
        // The main process has warned about the class already.
        $facade       = new WorkerExtensionFacade;
        $bootstrapper = new WorkerExtensionBootstrapper(ConfigurationRegistry::get(), $facade);

        $bootstrapper->bootstrap('PHPUnit\\TestFixture\\ParallelWorkerExtension\\ExtensionThatDoesNotExist', []);

        $this->assertSame([], $facade->subscribers());
        $this->assertSame([], $bootstrapper->warnings());
    }

    public function testRecordsAWarningWhenBootstrappingAnExtensionInTheWorkerFails(): void
    {
        $facade       = new WorkerExtensionFacade;
        $bootstrapper = new WorkerExtensionBootstrapper(ConfigurationRegistry::get(), $facade);

        $bootstrapper->bootstrap(FailingWorkerExtension::class, []);

        $warnings = $bootstrapper->warnings();

        $this->assertCount(1, $warnings);
        $this->assertStringStartsWith(
            'Bootstrapping of extension ' . FailingWorkerExtension::class . ' in a parallel worker process failed: the worker bootstrap failed',
            $warnings[0],
        );
    }

    public function testShutsDownAnExtensionThatWasBootstrapped(): void
    {
        $bootstrapper = new WorkerExtensionBootstrapper(ConfigurationRegistry::get(), new WorkerExtensionFacade);

        $bootstrapper->bootstrap(WorkerAwareExtension::class, []);

        $this->assertSame([], $bootstrapper->shutdown());
        $this->assertSame(1, WorkerAwareExtension::$shutdowns);
    }

    public function testDoesNotShutDownAnExtensionWhoseBootstrapFailed(): void
    {
        $bootstrapper = new WorkerExtensionBootstrapper(ConfigurationRegistry::get(), new WorkerExtensionFacade);

        $bootstrapper->bootstrap(FailingWorkerExtension::class, []);

        $this->assertSame([], $bootstrapper->shutdown());
    }

    public function testReturnsAWarningWhenShuttingDownAnExtensionFailsAndShutsDownTheOthersAllTheSame(): void
    {
        $bootstrapper = new WorkerExtensionBootstrapper(ConfigurationRegistry::get(), new WorkerExtensionFacade);

        $bootstrapper->bootstrap(ShutdownFailingWorkerExtension::class, []);
        $bootstrapper->bootstrap(WorkerAwareExtension::class, []);

        $warnings = $bootstrapper->shutdown();

        $this->assertCount(1, $warnings);
        $this->assertStringStartsWith(
            'Shutdown of extension ' . ShutdownFailingWorkerExtension::class . ' in a parallel worker process failed: the worker shutdown failed',
            $warnings[0],
        );
        $this->assertSame(1, WorkerAwareExtension::$shutdowns);
        $this->assertSame([], $bootstrapper->warnings());
    }
}
