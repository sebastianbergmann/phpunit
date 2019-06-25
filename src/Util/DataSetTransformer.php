<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\Framework\InvalidDataSetException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DataSetTransformer
{
    /**
     * @throws InvalidDataSetException
     */
    public static function transform(\ReflectionMethod $method, array $dataSet): array
    {
        if (!self::isAssociativeArray($dataSet)) {
            return $dataSet;
        }

        $argsKeys = \array_keys($dataSet);
        $origArgs = $dataSet;
        $newArgs  = [];

        foreach ($method->getParameters() as $parameter) {
            if (!\in_array($parameter->getName(), $argsKeys)) {
                if (!$parameter->isOptional()) {
                    throw new InvalidDataSetException(\sprintf('parameter $%s is not given', $parameter->getName()));
                }

                if ($parameter->isDefaultValueAvailable()) {
                    $newArgs[] = $parameter->getDefaultValue();
                }

                continue;
            }

            $paramValue = $origArgs[$parameter->getName()];
            unset($origArgs[$parameter->getName()]);

            if (!$parameter->isVariadic()) {
                $newArgs[] = $paramValue;

                continue;
            }

            if (!\is_array($paramValue) || self::isAssociativeArray($paramValue)) {
                throw new InvalidDataSetException(\sprintf(
                    'parameter $%s in %s::%s is variadic, non-associative array required, %s given',
                    $parameter->getName(),
                    $method->getDeclaringClass()->getName(),
                    $method->getName(),
                    \is_object($paramValue) ? \get_class($paramValue) : \gettype($paramValue)
                ));
            }

            $newArgs = \array_merge($newArgs, $paramValue);
        }

        if ([] !== $origArgs) {
            throw new InvalidDataSetException(\sprintf(
                'method %s::%s does not have the following parameters: %s',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                \implode(', ', \array_keys($origArgs))
            ));
        }

        return $newArgs;
    }

    private static function isAssociativeArray(array $array): bool
    {
        return !empty(\array_filter(\array_keys($array), 'is_string'));
    }
}
