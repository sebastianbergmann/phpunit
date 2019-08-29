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

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Text_Template;

final class MockMethod
{
    /**
     * @var Text_Template[]
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
     * @var string
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

        if ($method->hasReturnType()) {
            $returnType = $method->getReturnType()->getName();
        } else {
            $returnType = '';
        }

        $docComment = $method->getDocComment();

        if (\is_string($docComment)
            && \preg_match('#\*[ \t]*+@deprecated[ \t]*+(.*?)\r?+\n[ \t]*+\*(?:[ \t]*+@|/$)#s', $docComment, $deprecation)
        ) {
            $deprecation = \trim(\preg_replace('#[ \t]*\r?\n[ \t]*+\*[ \t]*+#', ' ', $deprecation[1]));
        } else {
            $deprecation = null;
        }

        return new self(
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $cloneArguments,
            $modifier,
            self::getMethodParameters($method),
            self::getMethodParameters($method, true),
            $returnType,
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
            '',
            '',
            false,
            false,
            null,
            false
        );
    }

    public function __construct(string $className, string $methodName, bool $cloneArguments, string $modifier, string $argumentsForDeclaration, string $argumentsForCall, string $returnType, string $reference, bool $callOriginalMethod, bool $static, ?string $deprecation, bool $allowsReturnNull)
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
     * @throws \ReflectionException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \InvalidArgumentException
     */
    public function generateCode(): string
    {
        if ($this->static) {
            $templateFile = 'mocked_static_method.tpl';
        } elseif ($this->returnType === 'void') {
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

        $returnType = $this->returnType;
        // @see https://bugs.php.net/bug.php?id=70722
        if ($returnType === 'self') {
            $returnType = $this->className;
        }

        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/406
        if ($returnType === 'parent') {
            $reflector = new ReflectionClass($this->className);

            $parentClass = $reflector->getParentClass();

            if ($parentClass === false) {
                throw new RuntimeException(
                    \sprintf(
                        'Cannot mock %s::%s because "parent" return type declaration is used but %s does not have a parent class',
                        $this->className,
                        $this->methodName,
                        $this->className
                    )
                );
            }

            $returnType = $parentClass->getName();
        }

        $deprecation = $this->deprecation;

        if (null !== $this->deprecation) {
            $deprecation         = "The $this->className::$this->methodName method is deprecated ($this->deprecation).";
            $deprecationTemplate = $this->getTemplate('deprecation.tpl');

            $deprecationTemplate->setVar([
                'deprecation' => \var_export($deprecation, true),
            ]);

            $deprecation = $deprecationTemplate->render();
        }

        $template = $this->getTemplate($templateFile);

        $template->setVar(
            [
                'arguments_decl'  => $this->argumentsForDeclaration,
                'arguments_call'  => $this->argumentsForCall,
                'return_delim'    => $returnType ? ': ' : '',
                'return_type'     => $this->allowsReturnNull ? '?' . $returnType : $returnType,
                'arguments_count' => !empty($this->argumentsForCall) ? \substr_count($this->argumentsForCall, ',') + 1 : 0,
                'class_name'      => $this->className,
                'method_name'     => $this->methodName,
                'modifier'        => $this->modifier,
                'reference'       => $this->reference,
                'clone_arguments' => $this->cloneArguments ? 'true' : 'false',
                'deprecation'     => $deprecation,
            ]
        );

        return $template->render();
    }

    private function getTemplate(string $template): Text_Template
    {
        $filename = __DIR__ . \DIRECTORY_SEPARATOR . 'Generator' . \DIRECTORY_SEPARATOR . $template;

        if (!isset(self::$templates[$filename])) {
            self::$templates[$filename] = new Text_Template($filename);
        }

        return self::$templates[$filename];
    }

    /**
     * Returns the parameters of a function or method.
     *
     * @throws RuntimeException
     */
    private static function getMethodParameters(ReflectionMethod $method, bool $forCall = false): string
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
                if ($forCall) {
                    continue;
                }

                $name = '...' . $name;
            }

            $nullable        = '';
            $default         = '';
            $reference       = '';
            $typeDeclaration = '';

            if (!$forCall) {
                if ($parameter->hasType() && $parameter->allowsNull()) {
                    $nullable = '?';
                }

                if ($parameter->hasType() && $parameter->getType()->getName() !== 'self') {
                    $typeDeclaration = $parameter->getType()->getName() . ' ';
                } else {
                    try {
                        $class = $parameter->getClass();
                    } catch (ReflectionException $e) {
                        throw new RuntimeException(
                            \sprintf(
                                'Cannot mock %s::%s() because a class or ' .
                                'interface used in the signature is not loaded',
                                $method->getDeclaringClass()->getName(),
                                $method->getName()
                            ),
                            0,
                            $e
                        );
                    }

                    if ($class !== null) {
                        $typeDeclaration = $class->getName() . ' ';
                    }
                }

                if (!$parameter->isVariadic()) {
                    if ($parameter->isDefaultValueAvailable()) {
                        try {
                            $value = \var_export($parameter->getDefaultValue(), true);
                        } catch (\ReflectionException $e) {
                            throw new RuntimeException(
                                $e->getMessage(),
                                (int) $e->getCode(),
                                $e
                            );
                        }

                        $default = ' = ' . $value;
                    } elseif ($parameter->isOptional()) {
                        $default = ' = null';
                    }
                }
            }

            if ($parameter->isPassedByReference()) {
                $reference = '&';
            }

            $parameters[] = $nullable . $typeDeclaration . $reference . $name . $default;
        }

        return \implode(', ', $parameters);
    }
}
