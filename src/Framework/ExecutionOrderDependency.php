<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function array_filter;
use function array_map;
use function array_values;
use function count;
use function explode;
use function in_array;
use function strpos;
use function trim;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExecutionOrderDependency
{
    /**
     * @var string
     */
    private $className = '';

    /**
     * @var string
     */
    private $methodName = '';

    /**
     * @var bool
     */
    private $useShallowClone = false;

    /**
     * @var bool
     */
    private $useDeepClone = false;

    public static function createFromDependsAnnotation(string $className, string $annotation): self
    {
        // Split clone option and target
        $parts = explode(' ', trim($annotation), 2);

        if (count($parts) === 1) {
            $cloneOption = '';
            $target      = $parts[0];
        } else {
            $cloneOption = $parts[0];
            $target      = $parts[1];
        }

        // Prefix provided class for targets assumed to be in scope
        if ($target !== '' && strpos($target, '::') === false) {
            $target = $className . '::' . $target;
        }

        return new self($target, null, $cloneOption);
    }

    /**
     * @psalm-param list<ExecutionOrderDependency> $dependencies
     *
     * @psalm-return list<ExecutionOrderDependency>
     */
    public static function filterInvalid(array $dependencies): array
    {
        return array_values(
            array_filter(
                $dependencies,
                static function (self $d)
                {
                    return $d->isValid();
                },
            ),
        );
    }

    /**
     * @psalm-param list<ExecutionOrderDependency> $existing
     * @psalm-param list<ExecutionOrderDependency> $additional
     *
     * @psalm-return list<ExecutionOrderDependency>
     */
    public static function mergeUnique(array $existing, array $additional): array
    {
        $existingTargets = array_map(
            static function ($dependency)
            {
                return $dependency->getTarget();
            },
            $existing,
        );

        foreach ($additional as $dependency) {
            if (in_array($dependency->getTarget(), $existingTargets, true)) {
                continue;
            }

            $existingTargets[] = $dependency->getTarget();
            $existing[]        = $dependency;
        }

        return $existing;
    }

    /**
     * @psalm-param list<ExecutionOrderDependency> $left
     * @psalm-param list<ExecutionOrderDependency> $right
     *
     * @psalm-return list<ExecutionOrderDependency>
     */
    public static function diff(array $left, array $right): array
    {
        if ($right === []) {
            return $left;
        }

        if ($left === []) {
            return [];
        }

        $diff         = [];
        $rightTargets = array_map(
            static function ($dependency)
            {
                return $dependency->getTarget();
            },
            $right,
        );

        foreach ($left as $dependency) {
            if (in_array($dependency->getTarget(), $rightTargets, true)) {
                continue;
            }

            $diff[] = $dependency;
        }

        return $diff;
    }

    public function __construct(string $classOrCallableName, ?string $methodName = null, ?string $option = null)
    {
        if ($classOrCallableName === '') {
            return;
        }

        if (strpos($classOrCallableName, '::') !== false) {
            [$this->className, $this->methodName] = explode('::', $classOrCallableName);
        } else {
            $this->className  = $classOrCallableName;
            $this->methodName = !empty($methodName) ? $methodName : 'class';
        }

        if ($option === 'clone') {
            $this->useDeepClone = true;
        } elseif ($option === 'shallowClone') {
            $this->useShallowClone = true;
        }
    }

    public function __toString(): string
    {
        return $this->getTarget();
    }

    public function isValid(): bool
    {
        // Invalid dependencies can be declared and are skipped by the runner
        return $this->className !== '' && $this->methodName !== '';
    }

    public function useShallowClone(): bool
    {
        return $this->useShallowClone;
    }

    public function useDeepClone(): bool
    {
        return $this->useDeepClone;
    }

    public function targetIsClass(): bool
    {
        return $this->methodName === 'class';
    }

    public function getTarget(): string
    {
        return $this->isValid()
            ? $this->className . '::' . $this->methodName
            : '';
    }

    public function getTargetClassName(): string
    {
        return $this->className;
    }
}
