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
use function is_a;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;
use function str_starts_with;
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
            if ($testWith->isNotEmpty()) {
                $method = new ReflectionMethod($className, $methodName);

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
                    'Mixing #[DataProvider*] and #[TestWith*] attributes is not supported, only the data provided by #[DataProvider*] will be used',
                );
            }
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
                        'Data set %s provided by %s has more arguments (%d) than the test method accepts (%d)',
                        $this->formatKey($key),
                        $providedData->label(),
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

        /**
         * @var array<ProvidedData> $result
         */
        $result = [];

        foreach ($dataProvider as $_dataProvider) {
            assert($_dataProvider instanceof DataProviderMetadata);

            if (is_a($_dataProvider->className(), TestCase::class, true) &&
                str_starts_with($_dataProvider->methodName(), 'test')) {
                Event\Facade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'The name of the data provider method %s::%s() used by test method %s::%s() begins with "test", therefore PHPUnit also treats it as a test method',
                        $_dataProvider->className(),
                        $_dataProvider->methodName(),
                        $className,
                        $methodName,
                    ),
                );
            }

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
                                $result[$key]->label(),
                            ),
                        );
                    }

                    $result[$key] = new ProvidedData($providerLabel, $value);
                } else {
                    Event\Facade::emitter()->dataProviderMethodFinished(
                        $testMethod,
                        ...$methodsCalled,
                    );

                    throw new InvalidDataProviderException(
                        sprintf(
                            'The key must be an integer or a string, %s given',
                            get_debug_type($key),
                        ),
                    );
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
