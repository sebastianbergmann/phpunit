<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Parser;

use function assert;
use function class_exists;
use function method_exists;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Metadata\MetadataCollection;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ParserChain implements Parser
{
    private readonly Parser $attributeReader;
    private readonly Parser $annotationReader;

    public function __construct(Parser $attributeReader, Parser $annotationReader)
    {
        $this->attributeReader  = $attributeReader;
        $this->annotationReader = $annotationReader;
    }

    /**
     * @psalm-param class-string $className
     */
    public function forClass(string $className): MetadataCollection
    {
        assert(class_exists($className));

        $attributes  = $this->attributeReader->forClass($className);
        $annotations = $this->annotationReader->forClass($className);

        if (!$attributes->isEmpty()) {
            if (!$annotations->isEmpty()) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    'PHPDoc annotations will be ignored when attributes are declared',
                );
            }

            return $attributes;
        }

        return $annotations;
    }

    /**
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     */
    public function forMethod(string $className, string $methodName): MetadataCollection
    {
        assert(class_exists($className));
        assert(method_exists($className, $methodName));

        $attributes  = $this->attributeReader->forMethod($className, $methodName);
        $annotations = $this->annotationReader->forMethod($className, $methodName);

        if (!$attributes->isEmpty()) {
            if (!$annotations->isEmpty()) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    'PHPDoc annotations will be ignored when attributes are declared',
                );
            }

            return $attributes;
        }

        return $annotations;
    }

    /**
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     */
    public function forClassAndMethod(string $className, string $methodName): MetadataCollection
    {
        return $this->forClass($className)->mergeWith(
            $this->forMethod($className, $methodName),
        );
    }
}
