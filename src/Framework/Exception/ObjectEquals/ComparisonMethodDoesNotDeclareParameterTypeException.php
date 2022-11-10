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

use const PHP_EOL;
use function sprintf;
use Stringable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ComparisonMethodDoesNotDeclareParameterTypeException extends Exception implements Stringable
{
    public function __construct(string $className, string $methodName)
    {
        parent::__construct(
            sprintf(
                'Parameter of comparison method %s::%s() does not have a declared type.',
                $className,
                $methodName
            )
        );
    }

    public function __toString(): string
    {
        return $this->getMessage() . PHP_EOL;
    }
}
