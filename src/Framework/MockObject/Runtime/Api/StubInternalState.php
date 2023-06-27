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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class StubInternalState
{
    /**
     * @var array<class-string, list<ConfigurableMethod>>
     */
    private static array $configurableMethods    = [];
    private bool $returnValueGeneration          = true;
    private ?InvocationHandler $invocationMocker = null;

    /**
     * @param class-string             $class
     * @param list<ConfigurableMethod> $configurableMethods
     */
    public static function initConfigurableMethods(string $class, array $configurableMethods): void
    {
        self::$configurableMethods[$class] = $configurableMethods;
    }

    public function setReturnValueGeneration(bool $returnValueGeneration): void
    {
        $this->returnValueGeneration = $returnValueGeneration;
    }

    /**
     * @param class-string $class
     */
    public function getInvocationHandler(string $class): InvocationHandler
    {
        if ($this->invocationMocker === null) {
            $this->invocationMocker = new InvocationHandler(
                self::$configurableMethods[$class] ?? [],
                $this->returnValueGeneration,
            );
        }

        return $this->invocationMocker;
    }

    public function unsetInvocationHandler(): void
    {
        $this->invocationMocker = null;
    }

    public function cloneInvocationHandler(): void
    {
        if ($this->invocationMocker !== null) {
            $this->invocationMocker = clone $this->invocationMocker;
        }
    }
}
