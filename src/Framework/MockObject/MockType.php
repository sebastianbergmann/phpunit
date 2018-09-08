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

use Text_Template;

final class MockType
{
    /**
     * @var Text_Template[]
     */
    private static $templates = [];

    /**
     * @var OriginalType
     */
    private $originalType;

    /**
     * @var TypeName
     */
    private $className;

    /**
     * @var MockMethodSet
     */
    private $methods;

    /**
     * @var bool
     */
    private $callAutoload;

    /**
     * @var string[]
     */
    private $interfaces;

    /**
     * @var bool
     */
    private $callOriginalClone;

    public function __construct(OriginalType $originalType, TypeName $className, array $interfaces, MockMethodSet $methods, bool $callAutoload, bool $callOriginalClone)
    {
        $this->originalType      = $originalType;
        $this->className         = $className;
        $this->methods           = $methods;
        $this->interfaces        = $interfaces;
        $this->callAutoload      = $callAutoload;
        $this->callOriginalClone = $callOriginalClone;
    }

    public function generateCode(): string
    {
        $classTemplate = $this->getTemplate('mocked_class.tpl');

        $method = '';

        if (!$this->methods->hasMethod('method') && !$this->originalType->hasMethod('method')) {
            $methodTemplate = $this->getTemplate('mocked_class_method.tpl');
            $method         = $methodTemplate->render();
        }

        $mockedMethodCode = '';
        $configurable     = [];

        /** @var MockMethod $mockMethod */
        foreach ($this->methods->asArray() as $mockMethod) {
            $mockedMethodCode .= $mockMethod->generateCode();
            $configurable[] = \strtolower($mockMethod->getName());
        }

        $classTemplate->setVar(
            [
                'prologue'          => $this->originalType->getCodePrologue(),
                'epilogue'          => $this->originalType->getCodeEpilogue(),
                'class_declaration' => $this->generateMockClassDeclaration(),
                'clone'             => $this->generateCodeClone(),
                'mock_class_name'   => $this->getClassName()->getQualifiedName(),
                'mocked_methods'    => $mockedMethodCode,
                'method'            => $method,
                'configurable'      => '[' . \implode(
                        ', ',
                        \array_map(
                            function ($m) {
                                return '\'' . $m . '\'';
                            },
                            $configurable
                        )
                    ) . ']',
            ]
        );

        return $classTemplate->render();
    }

    public function getClassName(): TypeName
    {
        return $this->className;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getTemplate(string $template): Text_Template
    {
        $filename = __DIR__ . \DIRECTORY_SEPARATOR . 'Generator' . \DIRECTORY_SEPARATOR . $template;

        if (!isset(self::$templates[$filename])) {
            self::$templates[$filename] = new Text_Template($filename);
        }

        return self::$templates[$filename];
    }

    private function generateMockClassDeclaration(): string
    {
        $additionalInterfaces   = $this->interfaces;
        $additionalInterfaces[] = MockObject::class;

        $originalTypeName = $this->originalType->getName();

        if ($this->originalType->isInterface()) {
            if (!\in_array($originalTypeName->getSimpleName(), $additionalInterfaces)) {
                $additionalInterfaces[] = $originalTypeName->getQualifiedName();
            }

            return \sprintf(
                'class %s implements %s',
                $this->className->getSimpleName(),
                \implode(', ', $additionalInterfaces)
            );
        }

        return \sprintf(
                'class %s extends %s implements %s',
                $this->className->getSimpleName(),
                $originalTypeName->getQualifiedName(),
                \implode(', ', $additionalInterfaces)
            );
    }

    private function generateCodeClone(): string
    {
        $cloneTemplate = null;

        if ($this->originalType->hasMethod('__clone')) {
            $cloneMethod = $this->originalType->getMethod('__clone');

            if (!$cloneMethod->isFinal()) {
                if ($this->callOriginalClone && !$this->originalType->isInterface()) {
                    $cloneTemplate = $this->getTemplate('unmocked_clone.tpl');
                } else {
                    $cloneTemplate = $this->getTemplate('mocked_clone.tpl');
                }
            }
        } else {
            $cloneTemplate = $this->getTemplate('mocked_clone.tpl');
        }

        if (\is_object($cloneTemplate)) {
            return $cloneTemplate->render();
        }

        return '';
    }
}
