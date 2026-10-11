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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChoiceTree::class)]
#[CoversClass(ChoiceTreeNode::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ChoiceTreeTest extends TestCase
{
    public function testANodeIsExhaustedWhenAllOfItsChildrenAreExhausted(): void
    {
        $tree = new ChoiceTree;
        $root = $tree->root();

        $this->assertNotNull($root);
        $this->assertTrue($root->draws(1));

        $this->child($tree, $root, 0)->exhaust();

        $this->assertFalse($tree->isExhausted());

        $this->child($tree, $root, 1)->exhaust();

        $this->assertTrue($tree->isExhausted());
    }

    public function testANodeThatIsExhaustedAgainIsCountedOnlyOnceTowardsTheExhaustionOfItsParent(): void
    {
        $tree = new ChoiceTree;
        $root = $tree->root();

        $this->assertNotNull($root);
        $this->assertTrue($root->draws(1));

        $child = $this->child($tree, $root, 0);

        $child->exhaust();
        $child->exhaust();

        $this->assertFalse($tree->isExhausted());
    }

    public function testAChoiceThatLeadsToAnExhaustedNodeIsReplacedWithTheNextOneThatDoesNot(): void
    {
        $tree = new ChoiceTree;
        $root = $tree->root();

        $this->assertNotNull($root);
        $this->assertTrue($root->draws(3));

        $this->child($tree, $root, 1)->exhaust();
        $this->child($tree, $root, 3)->exhaust();

        $this->assertSame(0, $root->unexhaustedChoice(0));
        $this->assertSame(2, $root->unexhaustedChoice(1));
        $this->assertSame(0, $root->unexhaustedChoice(3));
    }

    public function testAChoiceThatIsDrawnFromAnotherRangeThanBeforeIsInconsistent(): void
    {
        $node = new ChoiceTreeNode(null);

        $this->assertTrue($node->draws(1));
        $this->assertTrue($node->draws(1));
        $this->assertFalse($node->draws(2));
    }

    public function testAChoiceThatIsDrawnWhereAnExampleEndedBeforeIsInconsistent(): void
    {
        $node = new ChoiceTreeNode(null);

        $node->exhaust();

        $this->assertFalse($node->draws(1));
    }

    public function testIsNoLongerUsedWhenItWouldHaveTooManyNodes(): void
    {
        $tree = new ChoiceTree(2);
        $root = $tree->root();

        $this->assertNotNull($root);
        $this->assertNotNull($tree->child($root, 0));
        $this->assertNull($tree->child($root, 1));
        $this->assertNull($tree->root());
        $this->assertNull($tree->child($root, 0));
        $this->assertFalse($tree->isExhausted());
    }

    /**
     * @param non-negative-int $choice
     */
    private function child(ChoiceTree $tree, ChoiceTreeNode $node, int $choice): ChoiceTreeNode
    {
        $child = $tree->child($node, $choice);

        $this->assertNotNull($child);

        return $child;
    }
}
