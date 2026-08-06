<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output;

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Extension\ExtensionCapabilities;
use PHPUnit\TextUI\CliArguments\Builder as CliBuilder;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use ReflectionProperty;

#[CoversClass(Facade::class)]
#[Medium]
#[Group('textui')]
final class FacadeTest extends TestCase
{
    /**
     * @var array<non-empty-string, mixed>
     */
    private array $state = [];

    protected function setUp(): void
    {
        foreach ($this->stateProperties() as $name) {
            $this->state[$name] = new ReflectionProperty(Facade::class, $name)->getValue();
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->state as $name => $value) {
            new ReflectionProperty(Facade::class, $name)->setValue(null, $value);
        }
    }

    public function testReusesPrinterForStandardOutput(): void
    {
        $printer = new NullPrinter;

        new ReflectionProperty(Facade::class, 'printer')->setValue(null, $printer);

        $this->assertNotSame($printer, Facade::printerFor('php://stdout'));

        $printer = DefaultPrinter::standardOutput();

        new ReflectionProperty(Facade::class, 'printer')->setValue(null, $printer);

        $this->assertSame($printer, Facade::printerFor('php://stdout'));
    }

    public function testCreatesPrinterForStandardErrorStream(): void
    {
        $printer = $this->init($this->configuration(['--stderr']));

        $this->assertInstanceOf(DefaultPrinter::class, $printer);
    }

    /**
     * @return list<non-empty-string>
     */
    private function stateProperties(): array
    {
        return [
            'printer',
            'compactResultPrinter',
            'defaultResultPrinter',
            'testDoxResultPrinter',
            'summaryPrinter',
            'defaultProgressPrinter',
        ];
    }

    /**
     * @param list<non-empty-string> $parameters
     */
    private function configuration(array $parameters): Configuration
    {
        return (new Merger)->merge(
            (new CliBuilder)->fromParameters($parameters),
            DefaultConfiguration::create(),
        );
    }

    /*
     * Facade::init() registers subscribers for the printers it creates. These
     * must not observe the test run that exercises Facade, so they are
     * registered with a throw-away event facade that is never forwarded.
     */
    private function init(Configuration $configuration): Printer
    {
        $property = new ReflectionProperty(EventFacade::class, 'instance');
        $facade   = $property->getValue();

        $property->setValue(null, new EventFacade);

        try {
            return Facade::init($configuration, ExtensionCapabilities::none());
        } finally {
            $property->setValue(null, $facade);
        }
    }
}
