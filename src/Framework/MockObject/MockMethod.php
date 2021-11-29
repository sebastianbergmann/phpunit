<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function implode;
use function is_string;
use function preg_match;
use function preg_replace;
use function sprintf;
use function substr_count;
use function trim;
use function var_export;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use SebastianBergmann\Type\ReflectionMapper;
use SebastianBergmann\Type\Type;
use SebastianBergmann\Type\UnknownType;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class MockMethod
{
    use TemplateLoader;
    private string $className;
    private string $methodName;
    private bool $cloneArguments;
    private string $modifier;
    private string $argumentsForDeclaration;
    private string $argumentsForCall;
    private Type $returnType;
    private string $reference;
    private bool $callOriginalMethod;
    private bool $static;
    private ?string $deprecation;

    /**
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public static function fromReflection(ReflectionMethod $method, bool $callOriginalMethod, bool $cloneArguments): self
    {
        if ($method->isPrivate()) {
            $modifier = 'private';
        } elseif ($method->isProtected()) {
            $modifier = 'protected';
        } else {
            $modifier = 'public';
        }

        if ($method->isStatic()) {
            $modifier .= ' static';
        }

        if ($method->returnsReference()) {
            $reference = '&';
        } else {
            $reference = '';
        }

        $docComment = $method->getDocComment();

        if (is_string($docComment) &&
            preg_match('#\*[ \t]*+@deprecated[ \t]*+(.*?)\r?+\n[ \t]*+\*(?:[ \t]*+@|/$)#s', $docComment, $deprecation)) {
            $deprecation = trim(preg_replace('#[ \t]*\r?\n[ \t]*+\*[ \t]*+#', ' ', $deprecation[1]));
        } else {
            $deprecation = null;
        }

        return new self(
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $cloneArguments,
            $modifier,
            self::getMethodParametersForDeclaration($method),
            self::getMethodParametersForCall($method),
            (new ReflectionMapper)->fromReturnType($method),
            $reference,
            $callOriginalMethod,
            $method->isStatic(),
            $deprecation
        );
    }

    public static function fromName(string $fullClassName, string $methodName, bool $cloneArguments): self
    {
        return new self(
            $fullClassName,
            $methodName,
            $cloneArguments,
            'public',
            '',
            '',
            new UnknownType,
            '',
            false,
            false,
            null
        );
    }

    public function __construct(string $className, string $methodName, bool $cloneArguments, string $modifier, string $argumentsForDeclaration, string $argumentsForCall, Type $returnType, string $reference, bool $callOriginalMethod, bool $static, ?string $deprecation)
    {
        $this->className               = $className;
        $this->methodName              = $methodName;
        $this->cloneArguments          = $cloneArguments;
        $this->modifier                = $modifier;
        $this->argumentsForDeclaration = $argumentsForDeclaration;
        $this->argumentsForCall        = $argumentsForCall;
        $this->returnType              = $returnType;
        $this->reference               = $reference;
        $this->callOriginalMethod      = $callOriginalMethod;
        $this->static                  = $static;
        $this->deprecation             = $deprecation;
    }

    public function getName(): string
    {
        return $this->methodName;
    }

    /**
     * @throws RuntimeException
     */
    public function generateCode(): string
    {
        if ($this->static) {
            $templateFile = 'mocked_static_method.tpl';
        } elseif ($this->returnType->isNever() || $this->returnType->isVoid()) {
            $templateFile = sprintf(
                '%s_method_never_or_void.tpl',
                $this->callOriginalMethod ? 'proxied' : 'mocked'
            );
        } else {
            $templateFile = sprintf(
                '%s_method.tpl',
                $this->callOriginalMethod ? 'proxied' : 'mocked'
            );
        }

        $deprecation = $this->deprecation;

        if (null !== $this->deprecation) {
            $deprecation         = "The {$this->className}::{$this->methodName} method is deprecated ({$this->deprecation}).";
            $deprecationTemplate = $this->loadTemplate('deprecation.tpl');

            $deprecationTemplate->setVar(
                [
                    'deprecation' => var_export($deprecation, true),
                ]
            );

            $deprecation = $deprecationTemplate->render();
        }

        $template = $this->loadTemplate($templateFile);

        $template->setVar(
            [
                'arguments_decl'     => $this->argumentsForDeclaration,
                'arguments_call'     => $this->argumentsForCall,
                'return_declaration' => !empty($this->returnType->asString()) ? (': ' . $this->returnType->asString()) : '',
                'return_type'        => $this->returnType->asString(),
                'arguments_count'    => !empty($this->argumentsForCall) ? substr_count($this->argumentsForCall, ',') + 1 : 0,
                'class_name'         => $this->className,
                'method_name'        => $this->methodName,
                'modifier'           => $this->modifier,
                'reference'          => $this->reference,
                'clone_arguments'    => $this->cloneArguments ? 'true' : 'false',
                'deprecation'        => $deprecation,
            ]
        );

        return $template->render();
    }

    public function getReturnType(): Type
    {
        return $this->returnType;
    }

    /**
     * Returns the parameters of a function or method.
     *
     * @throws RuntimeException
     */
    private static function getMethodParametersForDeclaration(ReflectionMethod $method): string
    {
        $parameters = [];

        foreach ($method->getParameters() as $i => $parameter) {
            $name = '$' . $parameter->getName();

            /* Note: PHP extensions may use empty names for reference arguments
             * or "..." for methods taking a variable number of arguments.
             */
            if ($name === '$' || $name === '$...') {
                $name = '$arg' . $i;
            }

            $nullable        = '';
            $default         = '';
            $reference       = '';
            $typeDeclaration = '';
            $type            = null;
            $typeName        = null;

            if ($parameter->hasType()) {
                $type = $parameter->getType();

                if ($type instanceof ReflectionNamedType) {
                    $typeName = $type->getName();
                }
            }

            if ($parameter->isVariadic()) {
                $name = '...' . $name;
            } elseif ($parameter->isDefaultValueAvailable()) {
                $default = ' = ' . self::exportDefaultValue($parameter);
            } elseif ($parameter->isOptional()) {
                $default = ' = null';
            }

            if ($type !== null) {
                if ($typeName !== 'mixed' && $parameter->allowsNull() && !$type instanceof ReflectionIntersectionType && !$type instanceof ReflectionUnionType) {
                    $nullable = '?';
                }

                if ($typeName === 'self') {
                    $typeDeclaration = $method->getDeclaringClass()->getName() . ' ';
                } elseif ($typeName !== null) {
                    $typeDeclaration = $typeName . ' ';
                } elseif ($type instanceof ReflectionUnionType) {
                    $typeDeclaration = self::unionTypeAsString(
                        $type,
                        $method->getDeclaringClass()->getName()
                    );
                } elseif ($type instanceof ReflectionIntersectionType) {
                    $typeDeclaration = self::intersectionTypeAsString($type);
                }
            }

            if ($parameter->isPassedByReference()) {
                $reference = '&';
            }

            $parameters[] = $nullable . $typeDeclaration . $reference . $name . $default;
        }

        return implode(', ', $parameters);
    }

    /**
     * Returns the parameters of a function or method.
     *
     * @throws ReflectionException
     */
    private static function getMethodParametersForCall(ReflectionMethod $method): string
    {
        $parameters = [];

        foreach ($method->getParameters() as $i => $parameter) {
            $name = '$' . $parameter->getName();

            /* Note: PHP extensions may use empty names for reference arguments
             * or "..." for methods taking a variable number of arguments.
             */
            if ($name === '$' || $name === '$...') {
                $name = '$arg' . $i;
            }

            if ($parameter->isVariadic()) {
                continue;
            }

            if ($parameter->isPassedByReference()) {
                $parameters[] = '&' . $name;
            } else {
                $parameters[] = $name;
            }
        }

        return implode(', ', $parameters);
    }

    /**
     * @throws ReflectionException
     */
    private static function exportDefaultValue(ReflectionParameter $parameter): string
    {
        try {
            return (string) var_export($parameter->getDefaultValue(), true);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd
    }

    private static function unionTypeAsString(ReflectionUnionType $union, string $self): string
    {
        $types = [];

        foreach ($union->getTypes() as $type) {
            if ((string) $type === 'self') {
                $types[] = $self;
            } else {
                $types[] = $type;
            }
        }

        return implode('|', $types) . ' ';
    }

    private static function intersectionTypeAsString(ReflectionIntersectionType $intersection): string
    {
        $types = [];

        foreach ($intersection->getTypes() as $type) {
            $types[] = $type;
        }

        return implode('&', $types) . ' ';
    }
}
