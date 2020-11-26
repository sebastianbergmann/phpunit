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

use SebastianBergmann\Type\ObjectType;
use SebastianBergmann\Type\Type;
use SebastianBergmann\Type\UnknownType;
use SebastianBergmann\Type\VoidType;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class MockMethod
{
    /**
     * @var \Text_Template[]
     */
    private static $templates = [];

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var bool
     */
    private $cloneArguments;

    /**
     * @var string string
     */
    private $modifier;

    /**
     * @var string
     */
    private $argumentsForDeclaration;

    /**
     * @var string
     */
    private $argumentsForCall;

    /**
     * @var Type
     */
    private $returnType;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var bool
     */
    private $callOriginalMethod;

    /**
     * @var bool
     */
    private $static;

    /**
     * @var ?string
     */
    private $deprecation;

    /**
     * @var bool
     */
    private $allowsReturnNull;

    /**
     * @throws RuntimeException
     */
    public static function fromReflection(\ReflectionMethod $method, bool $callOriginalMethod, bool $cloneArguments): self
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

        if (\is_string($docComment) &&
            \preg_match('#\*[ \t]*+@deprecated[ \t]*+(.*?)\r?+\n[ \t]*+\*(?:[ \t]*+@|/$)#s', $docComment, $deprecation)) {
            $deprecation = \trim(\preg_replace('#[ \t]*\r?\n[ \t]*+\*[ \t]*+#', ' ', $deprecation[1]));
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
            self::deriveReturnType($method),
            $reference,
            $callOriginalMethod,
            $method->isStatic(),
            $deprecation,
            $method->hasReturnType() && $method->getReturnType()->allowsNull()
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
            null,
            false
        );
    }

    public function __construct(string $className, string $methodName, bool $cloneArguments, string $modifier, string $argumentsForDeclaration, string $argumentsForCall, Type $returnType, string $reference, bool $callOriginalMethod, bool $static, ?string $deprecation, bool $allowsReturnNull)
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
        $this->allowsReturnNull        = $allowsReturnNull;
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
        } elseif ($this->returnType instanceof VoidType) {
            $templateFile = \sprintf(
                '%s_method_void.tpl',
                $this->callOriginalMethod ? 'proxied' : 'mocked'
            );
        } else {
            $templateFile = \sprintf(
                '%s_method.tpl',
                $this->callOriginalMethod ? 'proxied' : 'mocked'
            );
        }

        $deprecation = $this->deprecation;

        if (null !== $this->deprecation) {
            $deprecation         = "The {$this->className}::{$this->methodName} method is deprecated ({$this->deprecation}).";
            $deprecationTemplate = $this->getTemplate('deprecation.tpl');

            $deprecationTemplate->setVar([
                'deprecation' => \var_export($deprecation, true),
            ]);

            $deprecation = $deprecationTemplate->render();
        }

        $template = $this->getTemplate($templateFile);

        $template->setVar(
            [
                'arguments_decl'     => $this->argumentsForDeclaration,
                'arguments_call'     => $this->argumentsForCall,
                'return_declaration' => $this->returnType->getReturnTypeDeclaration(),
                'arguments_count'    => !empty($this->argumentsForCall) ? \substr_count($this->argumentsForCall, ',') + 1 : 0,
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

    private function getTemplate(string $template): \Text_Template
    {
        $filename = __DIR__ . \DIRECTORY_SEPARATOR . 'Generator' . \DIRECTORY_SEPARATOR . $template;

        if (!isset(self::$templates[$filename])) {
            self::$templates[$filename] = new \Text_Template($filename);
        }

        return self::$templates[$filename];
    }

    /**
     * Returns the parameters of a function or method.
     *
     * @throws RuntimeException
     */
    private static function getMethodParametersForDeclaration(\ReflectionMethod $method): string
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

                if ($type instanceof \ReflectionNamedType) {
                    $typeName = $type->getName();
                }
            }

            if ($parameter->isVariadic()) {
                $name = '...' . $name;
            } elseif ($parameter->isDefaultValueAvailable()) {
                $default = ' = ' . \var_export($parameter->getDefaultValue(), true);
            } elseif ($parameter->isOptional()) {
                $default = ' = null';
            }

            if ($type !== null) {
                if ($typeName !== 'mixed' && $parameter->allowsNull()) {
                    $nullable = '?';
                }

                if ($typeName === 'self') {
                    $typeDeclaration = $method->getDeclaringClass()->getName() . ' ';
                } elseif ($typeName !== null) {
                    $typeDeclaration = $typeName . ' ';
                }
            }

            if ($parameter->isPassedByReference()) {
                $reference = '&';
            }

            $parameters[] = $nullable . $typeDeclaration . $reference . $name . $default;
        }

        return \implode(', ', $parameters);
    }

    /**
     * Returns the parameters of a function or method.
     *
     * @throws \ReflectionException
     */
    private static function getMethodParametersForCall(\ReflectionMethod $method): string
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

        return \implode(', ', $parameters);
    }

    private static function deriveReturnType(\ReflectionMethod $method): Type
    {
        $returnType = $method->getReturnType();

        if ($returnType === null) {
            return new UnknownType();
        }

        // @see https://bugs.php.net/bug.php?id=70722
        if ($returnType->getName() === 'self') {
            return ObjectType::fromName($method->getDeclaringClass()->getName(), $returnType->allowsNull());
        }

        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/406
        if ($returnType->getName() === 'parent') {
            $parentClass = $method->getDeclaringClass()->getParentClass();

            if ($parentClass === false) {
                throw new RuntimeException(
                    \sprintf(
                        'Cannot mock %s::%s because "parent" return type declaration is used but %s does not have a parent class',
                        $method->getDeclaringClass()->getName(),
                        $method->getName(),
                        $method->getDeclaringClass()->getName()
                    )
                );
            }

            return ObjectType::fromName($parentClass->getName(), $returnType->allowsNull());
        }

        return Type::fromName($returnType->getName(), $returnType->allowsNull());
    }
}
