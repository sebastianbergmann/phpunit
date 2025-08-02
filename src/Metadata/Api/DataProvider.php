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
use function method_exists;
use function sprintf;
use PHPUnit\Event;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Framework\TestCase;
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
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     *
     * @throws InvalidDataProviderException
     *
     * @return ?array<ProvidedData>
     */
    public function providedData(string $className, string $methodName): ?array
    {
        assert(method_exists($className, $methodName));

        $dataProvider = MetadataRegistry::parser()->forMethod($className, $methodName)->isDataProvider();
        $testWith     = MetadataRegistry::parser()->forMethod($className, $methodName)->isTestWith();

        if ($dataProvider->isEmpty() && $testWith->isEmpty()) {
            return null;
        }

        $testMethod = new ReflectionMethod($className, $methodName);

        if ($dataProvider->isNotEmpty()) {
            if ($testWith->isNotEmpty()) {
                $this->triggerWarningForMixingOfDataProviderAndTestWith($testMethod);
            }

            $data = $this->dataProvidedByMethods($testMethod, $dataProvider);
        } else {
            $data = $this->dataProvidedByMetadata($testWith);
        }

        $testMethodNumberOfParameters = $testMethod->getNumberOfParameters();
        $testMethodIsNonVariadic      = !$testMethod->isVariadic();

        foreach ($data as $key => $providedData) {
            $value = $providedData->getData();

            if (!is_array($value)) {
                throw new InvalidDataProviderException(
                    sprintf(
                        'Data set %s provided by %s is invalid, expected array but got %s',
                        $this->formatKey($key),
                        $providedData->getProviderLabel(),
                        get_debug_type($value),
                    ),
                );
            }

            if ($testMethodIsNonVariadic && $testMethodNumberOfParameters < count($value)) {
                $this->triggerWarningForArgumentCount(
                    $testMethod,
                    $this->formatKey($key),
                    $providedData->getProviderLabel(),
                    count($value),
                    $testMethodNumberOfParameters,
                );
            }
        }

        return $data;
    }

    /**
     * @throws InvalidDataProviderException
     *
     * @return array<ProvidedData>
     */
    private function dataProvidedByMethods(ReflectionMethod $testMethod, MetadataCollection $dataProvider): array
    {
        $testMethod    = new Event\Code\ClassMethod($testMethod->getDeclaringClass()->getName(), $testMethod->getName());
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
                    throw new InvalidDataProviderException(
                        sprintf(
                            'Data Provider method %s::%s() is not public',
                            $_dataProvider->className(),
                            $_dataProvider->methodName(),
                        ),
                    );
                }

                if (!$method->isStatic()) {
                    throw new InvalidDataProviderException(
                        sprintf(
                            'Data Provider method %s::%s() is not static',
                            $_dataProvider->className(),
                            $_dataProvider->methodName(),
                        ),
                    );
                }

                if ($method->getNumberOfParameters() > 0) {
                    throw new InvalidDataProviderException(
                        sprintf(
                            'Data Provider method %s::%s() expects an argument',
                            $_dataProvider->className(),
                            $_dataProvider->methodName(),
                        ),
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

                        throw new InvalidDataProviderException(
                            sprintf(
                                'The key "%s" has already been defined by provider %s',
                                $key,
                                $result[$key]->getProviderLabel(),
                            ),
                        );
                    }

                    $result[$key] = new ProvidedData($providerLabel, $value);
                } else {
                    // @codeCoverageIgnoreStart
                    throw new InvalidDataProviderException(
                        sprintf(
                            'The key must be an integer or a string, %s given',
                            get_debug_type($key),
                        ),
                    );
                    // @codeCoverageIgnoreEnd
                }
            }
        }

        Event\Facade::emitter()->dataProviderMethodFinished(
            $testMethod,
            ...$methodsCalled,
        );

        if ($result === []) {
            throw new InvalidDataProviderException(
                'Empty data set provided by data provider',
            );
        }

        return $result;
    }

    /**
     * @return array<ProvidedData>
     */
    private function dataProvidedByMetadata(MetadataCollection $testWith): array
    {
        $result = [];

        foreach ($testWith as $i => $_testWith) {
            assert($_testWith instanceof TestWith);

            $providerLabel = sprintf('TestWith#%s attribute', $i);

            if ($_testWith->hasName()) {
                $key = $_testWith->name();

                if (array_key_exists($key, $result)) {
                    throw new InvalidDataProviderException(
                        sprintf(
                            'The key "%s" has already been defined by %s',
                            $key,
                            $result[$key]->getProviderLabel(),
                        ),
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

    private function triggerWarningForMixingOfDataProviderAndTestWith(ReflectionMethod $method): void
    {
        Event\Facade::emitter()->testTriggeredPhpunitWarning(
            $this->testValueObject($method),
            'Mixing #[DataProvider*] and #[TestWith*] attributes is not supported, only the data provided by #[DataProvider*] will be used',
        );
    }

    private function triggerWarningForArgumentCount(ReflectionMethod $method, string $key, string $label, int $numberOfValues, int $testMethodNumberOfParameters): void
    {
        Event\Facade::emitter()->testTriggeredPhpunitWarning(
            $this->testValueObject($method),
            sprintf(
                'Data set %s provided by %s has more arguments (%d) than the test method accepts (%d)',
                $key,
                $label,
                $numberOfValues,
                $testMethodNumberOfParameters,
            ),
        );
    }

    private function testValueObject(ReflectionMethod $method): TestMethod
    {
        return new TestMethod(
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $method->getFileName(),
            $method->getStartLine(),
            Event\Code\TestDoxBuilder::fromClassNameAndMethodName(
                $method->getDeclaringClass()->getName(),
                $method->getName(),
            ),
            MetadataCollection::fromArray([]),
            Event\TestData\TestDataCollection::fromArray([]),
        );
    }
}
