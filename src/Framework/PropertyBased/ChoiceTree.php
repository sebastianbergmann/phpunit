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

/**
 * The choice sequences of the examples of a property that have been
 * generated, as a tree in which each node stands for the choices that were
 * drawn before it, and the choices that can be drawn next are its children.
 *
 * A node is exhausted when every choice sequence that starts with its choices
 * has been generated: a leaf, at which an example ended, because the same
 * choices generate the same example again, and a node whose children are all
 * exhausted. Choices that lead to an exhausted node are not drawn again, so
 * that no example is generated twice, and no further example can be generated
 * once the root is exhausted.
 *
 * This relies on the generators to generate the same value from the same
 * choices. When they do not, because a generator draws a choice where it did
 * not before, the tree is no longer used. It is not used either once it has
 * too many nodes, which only a property whose examples consist of many
 * choices has, for which it is unlikely that an example is generated twice,
 * so that its memory is released.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ChoiceTree
{
    public const int DEFAULT_MAXIMUM_NUMBER_OF_NODES = 50000;

    /**
     * @var positive-int
     */
    private readonly int $maximumNumberOfNodes;
    private ?ChoiceTreeNode $root;

    /**
     * @var positive-int
     */
    private int $numberOfNodes = 1;

    /**
     * @param positive-int $maximumNumberOfNodes
     */
    public function __construct(int $maximumNumberOfNodes = self::DEFAULT_MAXIMUM_NUMBER_OF_NODES)
    {
        $this->maximumNumberOfNodes = $maximumNumberOfNodes;
        $this->root                 = new ChoiceTreeNode(null);
    }

    /**
     * The node for the choices that are drawn first, null when the tree is
     * no longer used.
     */
    public function root(): ?ChoiceTreeNode
    {
        return $this->root;
    }

    /**
     * Whether every example that can be generated has been generated.
     */
    public function isExhausted(): bool
    {
        return $this->root !== null && $this->root->isExhausted();
    }

    /**
     * Returns the child of the node for the choice, which is created when it
     * does not exist yet, or null when the tree is no longer used because it
     * would have too many nodes.
     *
     * @param non-negative-int $choice
     */
    public function child(ChoiceTreeNode $node, int $choice): ?ChoiceTreeNode
    {
        if ($this->root === null) {
            return null;
        }

        $child = $node->child($choice);

        if ($child !== null) {
            return $child;
        }

        if ($this->numberOfNodes >= $this->maximumNumberOfNodes) {
            $this->discard();

            return null;
        }

        $this->numberOfNodes++;

        return $node->addChild($choice);
    }

    /**
     * No longer uses the tree, because the generators do not generate the
     * same value from the same choices.
     */
    public function discard(): void
    {
        $this->root = null;
    }
}
