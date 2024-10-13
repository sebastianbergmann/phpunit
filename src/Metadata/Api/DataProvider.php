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

use const JSON_ERROR_NONE;
use const PREG_OFFSET_CAPTURE;
use function array_key_exists;
use function assert;
use function explode;
use function get_debug_type;
use function is_array;
use function is_int;
use function is_iterable;
use function is_string;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function preg_match;
use function preg_replace;
use function rtrim;
use function sprintf;
use function str_replace;
use function strlen;
use function substr;
use function trim;
use PHPUnit\Event;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Metadata\DataProvider as DataProviderMetadata;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Metadata\TestWith;
use ReflectionClass;
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
            return ['testWith' => $this->dataProvidedByTestWithAnnotation($className, $methodName)];
        }

        if ($dataProvider->isNotEmpty()) {
            $data = $this->dataProvidedByMethods($className, $methodName, $dataProvider);
        } else {
            $data = ['testWith' => $this->dataProvidedByMetadata($testWith)];
        }

        if ($data === [] || $data === ['testWith' => []]) {
            throw new InvalidDataProviderException(
                'Empty data set provided by data provider',
            );
        }

        foreach ($data as $providedData) {
            foreach ($providedData as $key => $value) {
                if (!is_array($value)) {
                    throw new InvalidDataProviderException(
                        sprintf(
                            'Data set %s is invalid, expected array but got %s',
                            is_int($key) ? '#' . $key : '"' . $key . '"',
                            get_debug_type($value),
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
                $class  = new ReflectionClass($providerClassName);
                $method = $class->getMethod($providerMethodName);

                if (!$method->isPublic()) {
                    $this->throwInvalid('is not public', $_dataProvider);
                }

                if (!$method->isStatic()) {
                    $this->throwInvalid('is not static', $_dataProvider);
                }

                if ($method->getNumberOfParameters() > 0) {
                    $this->throwInvalid('expects an argument', $_dataProvider);
                }

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
                    throw new InvalidDataProviderException(
                        sprintf(
                            'The key must be an integer or a string, %s given',
                            get_debug_type($key),
                        ),
                        method: $providerMethodName,
                    );
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
     * @param class-string $className
     *
     * @throws InvalidDataProviderException
     *
     * @return ?array<array<mixed>>
     */
    private function dataProvidedByTestWithAnnotation(string $className, string $methodName): ?array
    {
        $docComment = (new ReflectionMethod($className, $methodName))->getDocComment();

        if ($docComment === false) {
            return null;
        }

        $docComment = str_replace("\r\n", "\n", $docComment);
        $docComment = preg_replace('/\n\s*\*\s?/', "\n", $docComment);
        $docComment = substr($docComment, 0, -1);
        $docComment = rtrim($docComment, "\n");

        if (!preg_match('/@testWith\s+/', $docComment, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $offset            = strlen($matches[0][0]) + (int) $matches[0][1];
        $annotationContent = substr($docComment, $offset);
        $data              = [];

        foreach (explode("\n", $annotationContent) as $candidateRow) {
            $candidateRow = trim($candidateRow);

            if ($candidateRow === '' || $candidateRow[0] !== '[') {
                break;
            }

            $dataSet = json_decode($candidateRow, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidDataProviderException(
                    'The data set for the @testWith annotation cannot be parsed: ' . json_last_error_msg(),
                );
            }

            $data[] = $dataSet;
        }

        if (!$data) {
            throw new InvalidDataProviderException(
                'The data set for the @testWith annotation cannot be parsed.',
            );
        }

        return $data;
    }
}
