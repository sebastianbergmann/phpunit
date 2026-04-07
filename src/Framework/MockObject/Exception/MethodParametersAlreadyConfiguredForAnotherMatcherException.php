<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function sprintf;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class MethodParametersAlreadyConfiguredForAnotherMatcherException extends \PHPUnit\Framework\Exception implements Exception
{
    public function __construct(string $methodName)
    {
        parent::__construct(
            sprintf(
                'Parameters for method "%s" are already configured for another matcher. ' .
                'with() configures an expectation (the method must be called with the specified arguments), ' .
                'it does not select a return value based on arguments. ' .
                'Use willReturnMap() to return different values based on arguments.',
                $methodName,
            ),
        );
    }
}
