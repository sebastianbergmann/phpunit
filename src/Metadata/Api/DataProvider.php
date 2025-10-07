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
use function is_iterable;
use function is_string;
use function sprintf;
use PHPUnit\Event;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\DataProvider as DataProviderMetadata;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Metadata\TestWith;
use PHPUnit\Util\Test;
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
        $metadataCollection = MetadataRegistry::parser()->forMethod($className, $methodName);
        $dataProvider       = $metadataCollection->isDataProvider();
        $testWith           = $metadataCollection->isTestWith();

        if ($dataProvider->isEmpty() && $testWith->isEmpty()) {
            return null;
        }

        $testMethod = new ReflectionMethod($className, $methodName);

        if ($dataProvider->isNotEmpty()) {
            if ($testWith->isNotEmpty()) {
                $this->triggerWarningForMixingOfDataProviderAndTestWith($testMethod);
            }

            return $this->dataProvidedByMethods($className, $testMethod, $dataProvider);
        }

        return $this->dataProvidedByMetadata($testMethod, $testWith);
    }

    /**
     * @param class-string<TestCase> $testClassName
     *
     * @throws InvalidDataProviderException
     *
     * @return array<ProvidedData>
     */
    private function dataProvidedByMethods(string $testClassName, ReflectionMethod $testMethod, MetadataCollection $dataProvider): array
    {
        $testMethodValueObject = new Event\Code\ClassMethod(
            $testClassName,
            $testMethod->getName(),
        );

        $methodsCalled                = [];
        $result                       = [];
        $testMethodNumberOfParameters = $testMethod->getNumberOfParameters();
        $testMethodIsNonVariadic      = !$testMethod->isVariadic();

        foreach ($dataProvider as $_dataProvider) {
            assert($_dataProvider instanceof DataProviderMetadata);

            $providerLabel         = $_dataProvider->className() . '::' . $_dataProvider->methodName();
            $dataProviderMethod    = new Event\Code\ClassMethod($_dataProvider->className(), $_dataProvider->methodName());
            $validateArgumentCount = $testMethodIsNonVariadic && $_dataProvider->validateArgumentCount();

            Event\Facade::emitter()->dataProviderMethodCalled(
                $testMethodValueObject,
                $dataProviderMethod,
            );

            $methodsCalled[] = $dataProviderMethod;

            try {
                $method     = new ReflectionMethod($_dataProvider->className(), $_dataProvider->methodName());
                $className  = $_dataProvider->className();
                $methodName = $_dataProvider->methodName();

                if (Test::isTestMethod($method)) {
                    Event\Facade::emitter()->testRunnerTriggeredPhpunitWarning(
                        sprintf(
                            'Method %s::%s() used by test method %s::%s() is also a test method',
                            $_dataProvider->className(),
                            $_dataProvider->methodName(),
                            $testMethod->getDeclaringClass()->getName(),
                            $testMethod->getName(),
                        ),
                    );
                }

                if (!$method->isPublic()) {
                    throw new InvalidDataProviderException(
                        sprintf(
                            'Data Provider method %s::%s() is not public',
                            $className,
                            $methodName,
                        ),
                    );
                }

                if (!$method->isStatic()) {
                    throw new InvalidDataProviderException(
                        sprintf(
                            'Data Provider method %s::%s() is not static',
                            $className,
                            $methodName,
                        ),
                    );
                }

                if ($method->getNumberOfParameters() > 0) {
                    throw new InvalidDataProviderException(
                        sprintf(
                            'Data Provider method %s::%s() expects an argument',
                            $className,
                            $methodName,
                        ),
                    );
                }

                /** @phpstan-ignore staticMethod.dynamicName */
                $data = $className::$methodName();

                if (!is_iterable($data)) {
                    throw new InvalidDataProviderException(
                        sprintf(
                            'Data Provider method %s::%s() does not return an iterable',
                            $className,
                            $methodName,
                        ),
                    );
                }
            } catch (Throwable $e) {
                Event\Facade::emitter()->dataProviderMethodFinished(
                    $testMethodValueObject,
                    ...$methodsCalled,
                );

                throw InvalidDataProviderException::forException($e, $providerLabel);
            }

            foreach ($data as $key => $value) {
                if (!is_int($key) && !is_string($key)) {
                    Event\Facade::emitter()->dataProviderMethodFinished(
                        $testMethodValueObject,
                        ...$methodsCalled,
                    );

                    throw new InvalidDataProviderException(
                        sprintf(
                            'The key must be an integer or a string, %s given',
                            get_debug_type($key),
                        ),
                    );
                }

                if (!is_array($value)) {
                    Event\Facade::emitter()->dataProviderMethodFinished(
                        $testMethodValueObject,
                        ...$methodsCalled,
                    );

                    throw new InvalidDataProviderException(
                        sprintf(
                            'Data set %s provided by %s is invalid, expected array but got %s',
                            $this->formatKey($key),
                            $providerLabel,
                            get_debug_type($value),
                        ),
                    );
                }

                if ($validateArgumentCount && $testMethodNumberOfParameters < count($value)) {
                    $this->triggerWarningForArgumentCount(
                        $testMethod,
                        $this->formatKey($key),
                        $providerLabel,
                        count($value),
                        $testMethodNumberOfParameters,
                    );
                }

                if (is_int($key)) {
                    $result[] = new ProvidedData($providerLabel, $value);

                    continue;
                }

                if (array_key_exists($key, $result)) {
                    Event\Facade::emitter()->dataProviderMethodFinished(
                        $testMethodValueObject,
                        ...$methodsCalled,
                    );

                    throw new InvalidDataProviderException(
                        sprintf(
                            'The key "%s" has already been defined by provider %s',
                            $key,
                            $result[$key]->label(),
                        ),
                    );
                }

                $result[$key] = new ProvidedData($providerLabel, $value);
            }
        }

        Event\Facade::emitter()->dataProviderMethodFinished(
            $testMethodValueObject,
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
    private function dataProvidedByMetadata(ReflectionMethod $testMethod, MetadataCollection $testWith): array
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
                            $result[$key]->label(),
                        ),
                    );
                }

                $result[$key] = new ProvidedData($providerLabel, $_testWith->data());
            } else {
                $result[] = new ProvidedData($providerLabel, $_testWith->data());
            }
        }

        $testMethodNumberOfParameters = $testMethod->getNumberOfParameters();
        $testMethodIsNonVariadic      = !$testMethod->isVariadic();

        foreach ($result as $key => $providedData) {
            $value = $providedData->value();

            if (!is_array($value)) {
                throw new InvalidDataProviderException(
                    sprintf(
                        'Data set %s provided by %s is invalid, expected array but got %s',
                        $this->formatKey($key),
                        $providedData->label(),
                        get_debug_type($value),
                    ),
                );
            }

            if ($testMethodIsNonVariadic && $testMethodNumberOfParameters < count($value)) {
                $this->triggerWarningForArgumentCount(
                    $testMethod,
                    $this->formatKey($key),
                    $providedData->label(),
                    count($value),
                    $testMethodNumberOfParameters,
                );
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
