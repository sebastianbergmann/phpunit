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

use function array_reverse;
use function class_exists;
use function count;
use function explode;
use function function_exists;
use function method_exists;
use function sprintf;
use PHPUnit\Event\Emitter;
use PHPUnit\Runner\DeprecationFilter;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\IssueTriggerResolver\Resolver;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Configures PHPUnit's error handler with the deprecation triggers, the issue
 * trigger resolvers, and the deprecation filters of the configuration.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ErrorHandlerConfigurator
{
    private Emitter $emitter;

    public function __construct(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    public function configure(Configuration $configuration): void
    {
        $this->deprecationTriggers($configuration);
        $this->issueTriggerResolvers($configuration);
        $this->deprecationFilters($configuration);
    }

    private function deprecationTriggers(Configuration $configuration): void
    {
        $deprecationTriggers = [
            'functions' => [],
            'methods'   => [],
        ];

        $ignoreUndefinedTriggers = $configuration->source()->deprecationTriggers()['ignoreUndefinedTriggers'] ?? false;

        foreach ($configuration->source()->deprecationTriggers()['functions'] as $function) {
            if (!function_exists($function)) {
                if (!$ignoreUndefinedTriggers) {
                    $this->emitter->testRunnerTriggeredPhpunitWarning(
                        sprintf(
                            'Function %s cannot be configured as a deprecation trigger because it is not declared',
                            $function,
                        ),
                    );
                }

                continue;
            }

            $deprecationTriggers['functions'][] = $function;
        }

        foreach ($configuration->source()->deprecationTriggers()['methods'] as $method) {
            $parts = explode('::', $method, 2);

            if (count($parts) !== 2) {
                $this->emitter->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        '%s cannot be configured as a deprecation trigger because it is not in ClassName::methodName format',
                        $method,
                    ),
                );

                continue;
            }

            [$className, $methodName] = $parts;

            if ($methodName === '' || !class_exists($className) || !method_exists($className, $methodName)) {
                if (!$ignoreUndefinedTriggers) {
                    $this->emitter->testRunnerTriggeredPhpunitWarning(
                        sprintf(
                            'Method %s::%s cannot be configured as a deprecation trigger because it is not declared',
                            $className,
                            $methodName,
                        ),
                    );
                }

                continue;
            }

            $deprecationTriggers['methods'][] = [
                'className'  => $className,
                'methodName' => $methodName,
            ];
        }

        if ($deprecationTriggers !== ['functions' => [], 'methods' => []]) {
            ErrorHandler::instance()->useDeprecationTriggers($deprecationTriggers);
        }
    }

    private function issueTriggerResolvers(Configuration $configuration): void
    {
        $classNames = $configuration->source()->issueTriggerResolvers();

        foreach (array_reverse($classNames) as $className) {
            if (!class_exists($className)) {
                $this->emitter->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Class %s cannot be used as an issue trigger resolver because it does not exist',
                        $className,
                    ),
                );

                continue;
            }

            $resolver = new $className;

            if (!$resolver instanceof Resolver) {
                $this->emitter->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Class %s cannot be used as an issue trigger resolver because it does not implement %s',
                        $className,
                        Resolver::class,
                    ),
                );

                continue;
            }

            ErrorHandler::instance()->addIssueTriggerResolver($resolver);
        }
    }

    private function deprecationFilters(Configuration $configuration): void
    {
        foreach ($configuration->source()->deprecationFilters() as $className) {
            if (!class_exists($className)) {
                $this->emitter->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Class %s cannot be used as a deprecation filter because it does not exist',
                        $className,
                    ),
                );

                continue;
            }

            $filter = new $className;

            if (!$filter instanceof DeprecationFilter) {
                $this->emitter->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Class %s cannot be used as a deprecation filter because it does not implement %s',
                        $className,
                        DeprecationFilter::class,
                    ),
                );

                continue;
            }

            ErrorHandler::instance()->addDeprecationFilter($filter);
        }
    }
}
