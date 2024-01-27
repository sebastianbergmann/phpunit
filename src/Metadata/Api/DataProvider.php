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
use function array_merge;
use function assert;
use function explode;
use function is_array;
use function is_int;
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
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Metadata\DataProvider as DataProviderMetadata;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Metadata\TestWith;
use ReflectionClass;
use ReflectionMethod;
use Throwable;
use Traversable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DataProvider
{
    /**
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     *
     * @throws InvalidDataProviderException
     */
    public function providedData(string $className, string $methodName): ?array
    {
        $dataProvider = MetadataRegistry::parser()->forMethod($className, $methodName)->isDataProvider();
        $testWith     = MetadataRegistry::parser()->forMethod($className, $methodName)->isTestWith();

        if ($dataProvider->isEmpty() && $testWith->isEmpty()) {
            return $this->dataProvidedByTestWithAnnotation($className, $methodName);
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

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                throw new InvalidDataProviderException(
                    sprintf(
                        'Data set %s is invalid',
                        is_int($key) ? '#' . $key : '"' . $key . '"',
                    ),
                );
            }
        }

        return $data;
    }

    /**
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     *
     * @throws InvalidDataProviderException
     */
    private function dataProvidedByMethods(string $className, string $methodName, MetadataCollection $dataProvider): array
    {
        $testMethod    = new Event\Code\ClassMethod($className, $methodName);
        $methodsCalled = [];
        $result        = [];

        foreach ($dataProvider as $_dataProvider) {
            assert($_dataProvider instanceof DataProviderMetadata);

            $dataProviderMethod = new Event\Code\ClassMethod($_dataProvider->className(), $_dataProvider->methodName());

            Event\Facade::emitter()->dataProviderMethodCalled(
                $testMethod,
                $dataProviderMethod,
            );

            $methodsCalled[] = $dataProviderMethod;

            try {
                $class  = new ReflectionClass($_dataProvider->className());
                $method = $class->getMethod($_dataProvider->methodName());
                $object = null;

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

                $data = $method->invoke($object);
            } catch (Throwable $e) {
                Event\Facade::emitter()->dataProviderMethodFinished(
                    $testMethod,
                    ...$methodsCalled,
                );

                throw new InvalidDataProviderException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e,
                );
            }

            if ($data instanceof Traversable) {
                $origData = $data;
                $data     = [];

                foreach ($origData as $key => $value) {
                    if (is_int($key)) {
                        $data[] = $value;
                    } elseif (array_key_exists($key, $data)) {
                        Event\Facade::emitter()->dataProviderMethodFinished(
                            $testMethod,
                            ...$methodsCalled,
                        );

                        throw new InvalidDataProviderException(
                            sprintf(
                                'The key "%s" has already been defined by a previous data provider',
                                $key,
                            ),
                        );
                    } else {
                        $data[$key] = $value;
                    }
                }
            }

            if (is_array($data)) {
                $result = array_merge($result, $data);
            }
        }

        Event\Facade::emitter()->dataProviderMethodFinished(
            $testMethod,
            ...$methodsCalled,
        );

        return $result;
    }

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
     * @psalm-param class-string $className
     *
     * @throws InvalidDataProviderException
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

            if ($candidateRow[0] !== '[') {
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
