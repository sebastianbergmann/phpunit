<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

use function array_reverse;
use function class_exists;
use function count;
use function explode;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\IssueTriggerResolver\Resolver;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ErrorHandlerBootstrapper
{
    public static function bootstrap(Configuration $configuration): void
    {
        $deprecationTriggers = [
            'functions' => [],
            'methods'   => [],
        ];

        foreach ($configuration->source()->deprecationTriggers()['functions'] as $function) {
            $deprecationTriggers['functions'][] = $function;
        }

        foreach ($configuration->source()->deprecationTriggers()['methods'] as $method) {
            $parts = explode('::', $method, 2);

            if (count($parts) !== 2) {
                continue;
            }

            [$className, $methodName] = $parts;

            if ($methodName === '') {
                continue;
            }

            $deprecationTriggers['methods'][] = [
                'className'  => $className,
                'methodName' => $methodName,
            ];
        }

        ErrorHandler::instance()->useDeprecationTriggers($deprecationTriggers);

        foreach (array_reverse($configuration->source()->issueTriggerResolvers()) as $className) {
            if (!class_exists($className)) {
                continue;
            }

            $resolver = new $className;

            if (!$resolver instanceof Resolver) {
                continue;
            }

            ErrorHandler::instance()->addIssueTriggerResolver($resolver);
        }
    }
}
