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
use function explode;
use PHPUnit\Runner\ErrorHandler;
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
            [$className, $methodName] = explode('::', $method);

            $deprecationTriggers['methods'][] = [
                'className'  => $className,
                'methodName' => $methodName,
            ];
        }

        ErrorHandler::instance()->useDeprecationTriggers($deprecationTriggers);

        foreach (array_reverse($configuration->source()->issueTriggerResolvers()) as $className) {
            ErrorHandler::instance()->addIssueTriggerResolver(new $className);
        }
    }
}
