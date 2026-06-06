<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestRunner\ErrorHandlerBootstrapper;
use PHPUnit\Runner\DeprecationFilter;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\IssueTriggerResolver\DefaultResolver;
use PHPUnit\Runner\IssueTriggerResolver\Resolver;
use PHPUnit\TestFixture\DeprecationFilter\FilterA;
use PHPUnit\TestFixture\DeprecationFilter\FilterB;
use PHPUnit\TestFixture\IssueTriggerResolver\ResolverA;
use PHPUnit\TestFixture\IssueTriggerResolver\ResolverB;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use ReflectionClass;

#[CoversClass(ErrorHandlerBootstrapper::class)]
#[Small]
final class ErrorHandlerBootstrapperTest extends TestCase
{
    #[TestDox('Configures empty deprecation triggers and leaves only the default issue trigger resolver registered when configuration is empty')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testBootstrapsWithEmptyConfiguration(): void
    {
        ErrorHandlerBootstrapper::bootstrap($this->configurationFromFixture('empty-source.xml'));

        $this->assertSame(
            ['functions' => [], 'methods' => []],
            $this->reflectProperty('deprecationTriggers'),
        );

        $resolvers = $this->reflectProperty('issueTriggerResolvers');

        $this->assertCount(1, $resolvers);
        $this->assertInstanceOf(DefaultResolver::class, $resolvers[0]);
    }

    #[TestDox('Parses function and Class::method deprecation triggers from configuration')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testBootstrapsDeprecationTriggers(): void
    {
        ErrorHandlerBootstrapper::bootstrap($this->configurationFromFixture('with-triggers.xml'));

        $this->assertSame(
            [
                'functions' => ['my_func'],
                'methods'   => [
                    [
                        'className'  => 'My\\Cls',
                        'methodName' => 'go',
                    ],
                ],
            ],
            $this->reflectProperty('deprecationTriggers'),
        );
    }

    #[TestDox('Registers configured issue trigger resolvers in the configured order, ahead of the default resolver')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testBootstrapsIssueTriggerResolversInConfiguredOrder(): void
    {
        ErrorHandlerBootstrapper::bootstrap($this->configurationFromFixture('with-resolvers.xml'));

        $resolvers = $this->reflectProperty('issueTriggerResolvers');

        $this->assertCount(3, $resolvers);
        $this->assertInstanceOf(ResolverA::class, $resolvers[0]);
        $this->assertInstanceOf(ResolverB::class, $resolvers[1]);
        $this->assertInstanceOf(DefaultResolver::class, $resolvers[2]);

        foreach ($resolvers as $resolver) {
            $this->assertInstanceOf(Resolver::class, $resolver);
        }
    }

    #[TestDox('Leaves no deprecation filters registered when configuration is empty')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testBootstrapsWithoutDeprecationFilters(): void
    {
        ErrorHandlerBootstrapper::bootstrap($this->configurationFromFixture('empty-source.xml'));

        $this->assertSame([], $this->reflectProperty('deprecationFilters'));
    }

    #[TestDox('Registers configured deprecation filters in the configured order')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testBootstrapsDeprecationFiltersInConfiguredOrder(): void
    {
        ErrorHandlerBootstrapper::bootstrap($this->configurationFromFixture('with-deprecation-filters.xml'));

        $filters = $this->reflectProperty('deprecationFilters');

        $this->assertCount(2, $filters);
        $this->assertInstanceOf(FilterA::class, $filters[0]);
        $this->assertInstanceOf(FilterB::class, $filters[1]);

        foreach ($filters as $filter) {
            $this->assertInstanceOf(DeprecationFilter::class, $filter);
        }
    }

    #[TestDox('Ignores a configured deprecation filter class that does not exist')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testIgnoresNonexistentDeprecationFilterClass(): void
    {
        ErrorHandlerBootstrapper::bootstrap($this->configurationFromFixture('with-nonexistent-deprecation-filter.xml'));

        $this->assertSame([], $this->reflectProperty('deprecationFilters'));
    }

    #[TestDox('Ignores a configured deprecation filter class that does not implement the interface')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testIgnoresDeprecationFilterNotImplementingTheInterface(): void
    {
        ErrorHandlerBootstrapper::bootstrap($this->configurationFromFixture('with-invalid-deprecation-filter.xml'));

        $this->assertSame([], $this->reflectProperty('deprecationFilters'));
    }

    #[TestDox('Ignores a configured deprecation filter with an empty class name')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testIgnoresDeprecationFilterWithEmptyClassName(): void
    {
        ErrorHandlerBootstrapper::bootstrap($this->configurationFromFixture('with-empty-deprecation-filter-classname.xml'));

        $this->assertSame([], $this->reflectProperty('deprecationFilters'));
    }

    private function configurationFromFixture(string $filename): Configuration
    {
        $fromFile = (new Loader)->load(__DIR__ . '/_files/' . $filename);
        $fromCli  = (new Builder)->fromParameters([]);

        return (new Merger)->merge($fromCli, $fromFile);
    }

    private function reflectProperty(string $name): mixed
    {
        $instance = ErrorHandler::instance();

        return new ReflectionClass($instance)->getProperty($name)->getValue($instance);
    }
}
