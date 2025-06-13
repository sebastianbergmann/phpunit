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
use function key;
use function reset;
use function sprintf;
use PHPUnit\Event;
use PHPUnit\Event\Code\ClassMethod;
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
     * @return ?array<array<mixed>>
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
            $data = ['testWith' => $this->dataProvidedByMetadata($testWith)];
        }

        if ($data === [] || reset($data) === []) {
            throw new InvalidDataProviderException(
                'Empty data set provided by data provider',
                method: key($data),
            );
        }

        $method                       = new ReflectionMethod($className, $methodName);
        $testMethodNumberOfParameters = $method->getNumberOfParameters();
        $testMethodIsNonVariadic      = !$method->isVariadic();

        foreach ($data as $providerMethodName => $providedData) {
            foreach ($providedData as $key => $value) {
                if (!is_array($value)) {
                    throw new InvalidDataProviderException(
                        sprintf(
                            'Data set %s is invalid, expected array but got %s',
                            $this->formatKey($key),
                            get_debug_type($value),
                        ),
                        method: $providerMethodName,
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
        }

        return $data;
    }

    /**
     * @param class-string     $testClassName  Name of class with test
     * @param non-empty-string $testMethodName Name of method containing test
     *
     * @throws InvalidDataProviderException
     *
     * @return array<array<mixed>>
     */
    private function dataProvidedByMethods(string $testClassName, string $testMethodName, MetadataCollection $dataProvider): array
    {
        $testMethod    = new ClassMethod($testClassName, $testMethodName);
        $methodsCalled = [];
        $return        = [];
        $caseNames     = [];

        foreach ($dataProvider as $_dataProvider) {
            assert($_dataProvider instanceof DataProviderMetadata);

            $providerClassName  = $_dataProvider->className();
            $providerMethodName = $_dataProvider->methodName();
            $dataProviderMethod = new ClassMethod($providerClassName, $providerMethodName);

            Event\Facade::emitter()->dataProviderMethodCalled(
                $testMethod,
                $dataProviderMethod,
            );

            $methodsCalled[] = $dataProviderMethod;

            try {
                $method = new ReflectionMethod($_dataProvider->className(), $_dataProvider->methodName());

                if (!$method->isPublic()) {
                    $this->throwInvalid('is not public', $_dataProvider);
                }

                if (!$method->isStatic()) {
                    $this->throwInvalid('is not static', $_dataProvider);
                }

                if ($method->getNumberOfParameters() > 0) {
                    $this->throwInvalid('expects an argument', $_dataProvider);
                }

                /** @phpstan-ignore staticMethod.dynamicName */
                $data = $providerClassName::$providerMethodName();

                if (!is_iterable($data)) {
                    $this->throwInvalid('does not provide iterable type', $_dataProvider);
                }
            } catch (Throwable $e) {
                $this->finishMethods($testMethod, $methodsCalled);

                throw new InvalidDataProviderException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e,
                    $providerMethodName,
                );
            }

            $result = [];

            foreach ($data as $key => $value) {
                if (is_int($key)) {
                    $result[] = $value;
                } elseif (is_string($key)) {
                    if (isset($caseNames[$key])) {
                        $this->finishMethods($testMethod, $methodsCalled);

                        throw new InvalidDataProviderException(
                            sprintf(
                                'The key "%s" has already been defined by a previous data provider',
                                $key,
                            ),
                            method: $providerMethodName,
                        );
                    }
                    $caseNames[$key] = 1;
                    $result[$key]    = $value;
                } else {
                    // @codeCoverageIgnoreStart
                    throw new InvalidDataProviderException(
                        sprintf(
                            'The key must be an integer or a string, %s given',
                            get_debug_type($key),
                        ),
                        method: $providerMethodName,
                    );
                    // @codeCoverageIgnoreEnd
                }
            }
            $return[$providerMethodName] = $result;
        }
        $this->finishMethods($testMethod, $methodsCalled);

        return $return;
    }

    private function throwInvalid(string $message, DataProviderMetadata $dataProvider): never
    {
        throw new InvalidDataProviderException(
            sprintf(
                'Data Provider method %s::%s() ',
                $dataProvider->className(),
                $dataProvider->methodName(),
            ) . $message,
        );
    }

    /**
     * @param ClassMethod[] $methodsCalled
     */
    private function finishMethods(ClassMethod $method, array $methodsCalled): void
    {
        Event\Facade::emitter()->dataProviderMethodFinished(
            $method,
            ...$methodsCalled,
        );
    }

    /**
     * @return array<array<mixed>>
     */
    private function dataProvidedByMetadata(MetadataCollection $testWith): array
    {
        $result = [];

        foreach ($testWith as $_testWith) {
            assert($_testWith instanceof TestWith);

            if ($_testWith->hasName()) {
                $key = $_testWith->name();

                if (array_key_exists($key, $result)) {
                    throw new InvalidDataProviderException(
                        sprintf(
                            'The key "%s" has already been defined by a previous TestWith attribute',
                            $key,
                        ),
                    );
                }

                $result[$key] = $_testWith->data();
            } else {
                $result[] = $_testWith->data();
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
