<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use Exception;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\TestRunHistory\NullTestRunHistory;
use PHPUnit\TextUI\CliArguments\Builder as CliBuilder;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use ReflectionProperty;

#[CoversClass(TestRunnerLifecycle::class)]
#[Medium]
#[Group('textui')]
final class TestRunnerLifecycleTest extends TestCase
{
    public function testWrapsAThrowableRaisedByTheExecutionInRuntimeException(): void
    {
        $configuration = (new Merger)->merge(
            (new CliBuilder)->fromParameters([]),
            DefaultConfiguration::create(),
        );

        /*
         * The lifecycle emits test runner events. These must not end up in the
         * result of the test run that exercises the lifecycle, so they are
         * emitted into a throw-away event facade that is never forwarded.
         */
        $property = new ReflectionProperty(EventFacade::class, 'instance');
        $facade   = $property->getValue();

        $property->setValue(null, new EventFacade);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('the execution failed');
            $this->expectExceptionCode(23);

            (new TestRunnerLifecycle)->run(
                $configuration,
                new NullTestRunHistory,
                TestSuite::empty('test suite'),
                static function (): void
                {
                    throw new Exception('the execution failed', 23);
                },
            );
        } finally {
            $property->setValue(null, $facade);
        }
    }
}
