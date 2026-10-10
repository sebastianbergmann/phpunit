<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function assert;

/**
 * A node of a ChoiceTree: the choices that were drawn before it, the range
 * that the next choice is drawn from, and the nodes for the choices that have
 * been drawn from it.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ChoiceTreeNode
{
    private readonly ?self $parent;

    /**
     * The largest choice that can be drawn next, null when no choice has been
     * drawn after the choices of this node yet.
     *
     * @var ?non-negative-int
     */
    private ?int $maximum = null;

    /**
     * @var array<non-negative-int, self>
     */
    private array $children = [];

    /**
     * @var non-negative-int
     */
    private int $numberOfExhaustedChildren = 0;
    private bool $exhausted                = false;

    public function __construct(?self $parent)
    {
        $this->parent = $parent;
    }

    public function isExhausted(): bool
    {
        return $this->exhausted;
    }

    /**
     * Records that a choice from the range [0, $maximum] is drawn after the
     * choices of this node, and returns whether this is consistent with the
     * choices that were drawn after them before: it is not when a different
     * range was used, or when an example ended with these choices.
     *
     * @param non-negative-int $maximum
     */
    public function draws(int $maximum): bool
    {
        if ($this->maximum === null) {
            if ($this->exhausted) {
                return false;
            }

            $this->maximum = $maximum;

            return true;
        }

        return $this->maximum === $maximum;
    }

    /**
     * Returns a choice that does not lead to an exhausted node: the given
     * one, or else the next larger one, wrapping around after the maximum.
     *
     * @param non-negative-int $choice
     *
     * @return non-negative-int
     */
    public function unexhaustedChoice(int $choice): int
    {
        if (!$this->leadsToExhaustedNode($choice)) {
            return $choice;
        }

        $maximum = $this->maximum;

        assert($maximum !== null && !$this->exhausted);

        do {
            if ($choice === $maximum) {
                $choice = 0;
            } else {
                $choice++;
            }
        } while ($this->leadsToExhaustedNode($choice));

        return $choice;
    }

    /**
     * @param non-negative-int $choice
     */
    public function leadsToExhaustedNode(int $choice): bool
    {
        return isset($this->children[$choice]) && $this->children[$choice]->exhausted;
    }

    /**
     * @param non-negative-int $choice
     */
    public function child(int $choice): ?self
    {
        return $this->children[$choice] ?? null;
    }

    /**
     * @param non-negative-int $choice
     */
    public function addChild(int $choice): self
    {
        $child = new self($this);

        $this->children[$choice] = $child;

        return $child;
    }

    /**
     * Records that every choice sequence that starts with the choices of this
     * node has been generated, which is the case for the node at which an
     * example ended, and for its parent when all of its children are
     * exhausted.
     */
    public function exhaust(): void
    {
        if ($this->exhausted) {
            return;
        }

        $this->exhausted = true;
        $this->children  = [];

        $this->parent?->childExhausted();
    }

    private function childExhausted(): void
    {
        $this->numberOfExhaustedChildren++;

        if ($this->maximum !== null && $this->numberOfExhaustedChildren > $this->maximum) {
            $this->exhaust();
        }
    }
}
