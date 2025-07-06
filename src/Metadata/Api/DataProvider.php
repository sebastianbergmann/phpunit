<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use function array_key_exists;
use function assert;
use function count;
use function get_debug_type;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;
use PHPUnit\Event;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Metadata\DataProvider as DataProviderMetadata;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Metadata\TestWith;
use ReflectionMethod;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DataProvider
{
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @throws InvalidDataProviderException
     *
     * @return ?array<ProvidedData>
     */
    public function providedData(string $className, string $methodName): ?array
    {
        $dataProvider = MetadataRegistry::parser()->forMethod($className, $methodName)->isDataProvider();
        $testWith     = MetadataRegistry::parser()->forMethod($className, $methodName)->isTestWith();

        if ($dataProvider->isEmpty() && $testWith->isEmpty()) {
            return null;
        }

        if ($dataProvider->isNotEmpty()) {
            $data = $this->dataProvidedByMethods($className, $methodName, $dataProvider);
        } else {
            $data = $this->dataProvidedByMetadata($testWith);
        }

        if ($data === []) {
            throw new InvalidDataProviderException(
                'Empty data set provided by data provider',
            );
        }

        $method                       = new ReflectionMethod($className, $methodName);
        $testMethodNumberOfParameters = $method->getNumberOfParameters();
        $testMethodIsNonVariadic      = !$method->isVariadic();

        foreach ($data as $key => $providedData) {
            $value = $providedData->getData();

            if (!is_array($value)) {
                throw InvalidDataProviderException::forProvider(
                    sprintf(
                        'Data set %s is invalid, expected array but got %s',
                        $this->formatKey($key),
                        get_debug_type($value),
                    ),
                    $providedData->getProviderLabel(),
                );
            }

            if ($testMethodIsNonVariadic && $testMethodNumberOfParameters < count($value)) {
                Event\Facade::emitter()->testTriggeredPhpunitWarning(
                    new TestMethod(
                        $className,
                        $methodName,
                        $method->getFileName(),
                        $method->getStartLine(),
                        Event\Code\TestDoxBuilder::fromClassNameAndMethodName(
                            $className,
                            $methodName,
                        ),
                        MetadataCollection::fromArray([]),
                        Event\TestData\TestDataCollection::fromArray([]),
                    ),
                    sprintf(
                        'Data set %s has more arguments (%d) than the test method accepts (%d)',
                        $this->formatKey($key),
                        count($value),
                        $testMethodNumberOfParameters,
                    ),
                );
            }
        }

        return $data;
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @throws InvalidDataProviderException
     *
     * @return array<ProvidedData>
     */
    private function dataProvidedByMethods(string $className, string $methodName, MetadataCollection $dataProvider): array
    {
        $testMethod    = new Event\Code\ClassMethod($className, $methodName);
        $methodsCalled = [];
        $result        = [];

        foreach ($dataProvider as $_dataProvider) {
            assert($_dataProvider instanceof DataProviderMetadata);

            $providerLabel      = $_dataProvider->className() . '::' . $_dataProvider->methodName();
            $dataProviderMethod = new Event\Code\ClassMethod($_dataProvider->className(), $_dataProvider->methodName());

            Event\Facade::emitter()->dataProviderMethodCalled(
                $testMethod,
                $dataProviderMethod,
            );

            $methodsCalled[] = $dataProviderMethod;

            try {
                $method = new ReflectionMethod($_dataProvider->className(), $_dataProvider->methodName());

                if (!$method->isPublic()) {
                    throw InvalidDataProviderException::forProvider(
                        sprintf(
                            'Data Provider method %s::%s() is not public',
                            $_dataProvider->className(),
                            $_dataProvider->methodName(),
                        ),
                        $providerLabel,
                    );
                }

                if (!$method->isStatic()) {
                    throw InvalidDataProviderException::forProvider(
                        sprintf(
                            'Data Provider method %s::%s() is not static',
                            $_dataProvider->className(),
                            $_dataProvider->methodName(),
                        ),
                        $providerLabel,
                    );
                }

                if ($method->getNumberOfParameters() > 0) {
                    throw InvalidDataProviderException::forProvider(
                        sprintf(
                            'Data Provider method %s::%s() expects an argument',
                            $_dataProvider->className(),
                            $_dataProvider->methodName(),
                        ),
                        $providerLabel,
                    );
                }

                $className  = $_dataProvider->className();
                $methodName = $_dataProvider->methodName();

                /** @phpstan-ignore staticMethod.dynamicName */
                $data = $className::$methodName();
            } catch (Throwable $e) {
                Event\Facade::emitter()->dataProviderMethodFinished(
                    $testMethod,
                    ...$methodsCalled,
                );

                throw InvalidDataProviderException::forException($e, $providerLabel);
            }

            foreach ($data as $key => $value) {
                if (is_int($key)) {
                    $result[] = new ProvidedData($providerLabel, $value);
                } elseif (is_string($key)) {
                    if (array_key_exists($key, $result)) {
                        Event\Facade::emitter()->dataProviderMethodFinished(
                            $testMethod,
                            ...$methodsCalled,
                        );

                        throw InvalidDataProviderException::forProvider(
                            sprintf(
                                'The key "%s" has already been defined by a previous data provider',
                                $key,
                            ),
                            $providerLabel,
                        );
                    }

                    $result[$key] = new ProvidedData($providerLabel, $value);
                } else {
                    // @codeCoverageIgnoreStart
                    throw InvalidDataProviderException::forProvider(
                        sprintf(
                            'The key must be an integer or a string, %s given',
                            get_debug_type($key),
                        ),
                        $providerLabel,
                    );
                    // @codeCoverageIgnoreEnd
                }
            }
        }

        Event\Facade::emitter()->dataProviderMethodFinished(
            $testMethod,
            ...$methodsCalled,
        );

        return $result;
    }

    /**
     * @return array<ProvidedData>
     */
    private function dataProvidedByMetadata(MetadataCollection $testWith): array
    {
        $result = [];

        $providerLabel = 'TestWith attribute';

        foreach ($testWith as $_testWith) {
            assert($_testWith instanceof TestWith);

            if ($_testWith->hasName()) {
                $key = $_testWith->name();

                if (array_key_exists($key, $result)) {
                    throw InvalidDataProviderException::forProvider(
                        sprintf(
                            'The key "%s" has already been defined by a previous TestWith attribute',
                            $key,
                        ),
                        $providerLabel,
                    );
                }

                $result[$key] = new ProvidedData($providerLabel, $_testWith->data());
            } else {
                $result[] = new ProvidedData($providerLabel, $_testWith->data());
            }
        }

        return $result;
    }

    /**
     * @param int|non-empty-string $key
     *
     * @return non-empty-string
     */
    private function formatKey(int|string $key): string
    {
        return is_int($key) ? '#' . $key : '"' . $key . '"';
    }
}
