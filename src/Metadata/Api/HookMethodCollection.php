<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use function array_map;
use function usort;
use ArrayObject;
use IteratorAggregate;
use Traversable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @implements IteratorAggregate<int, string>
 */
final class HookMethodCollection implements IteratorAggregate
{
    private readonly bool $shouldPrepend;

    /**
     * @var non-empty-list<HookMethod>
     */
    private array $hookMethods;

    public static function defaultBeforeClass(): self
    {
        return new self(new HookMethod('setUpBeforeClass', 0), true);
    }

    public static function defaultBefore(): self
    {
        return new self(new HookMethod('setUp', 0), true);
    }

    public static function defaultPreCondition(): self
    {
        return new self(new HookMethod('assertPreConditions', 0), true);
    }

    public static function defaultPostCondition(): self
    {
        return new self(new HookMethod('assertPostConditions', 0), false);
    }

    public static function defaultAfter(): self
    {
        return new self(new HookMethod('tearDown', 0), false);
    }

    public static function defaultAfterClass(): self
    {
        return new self(new HookMethod('tearDownAfterClass', 0), false);
    }

    private function __construct(HookMethod $default, bool $shouldPrepend)
    {
        $this->hookMethods   = [$default];
        $this->shouldPrepend = $shouldPrepend;
    }

    public function add(HookMethod $hookMethod): self
    {
        if ($this->shouldPrepend) {
            $this->hookMethods = [$hookMethod, ...$this->hookMethods];
        } else {
            $this->hookMethods[] = $hookMethod;
        }

        return $this;
    }

    public function getIterator(): Traversable
    {
        $hookMethods = $this->hookMethods;

        usort($hookMethods, static fn (HookMethod $a, HookMethod $b) => $b->priority() <=> $a->priority());

        return new ArrayObject(
            array_map(static fn (HookMethod $hookMethod) => $hookMethod->methodName(), $hookMethods),
        );
    }
}
