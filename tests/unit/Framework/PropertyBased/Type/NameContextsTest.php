<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Type;

use PhpParser\Node\Name;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(NameContexts::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class NameContextsTest extends TestCase
{
    public function testReturnsTheContextOfTheGlobalNamespaceForAFileThatCannotBeRead(): void
    {
        $context = NameContexts::at(__DIR__ . '/does-not-exist.php', 1);

        $this->assertNull($context->getNamespace());
        $this->assertSame('Other', $context->getResolvedClassName(new Name('Other'))->toString());
    }

    public function testReturnsTheContextOfTheGlobalNamespaceForALineThatIsNotPartOfADeclaration(): void
    {
        $context = NameContexts::at(__DIR__ . '/../../../../_files/PropertyBased/NameResolution/ClassWithImports.php', 1);

        $this->assertNull($context->getNamespace());
        $this->assertSame('Other', $context->getResolvedClassName(new Name('Other'))->toString());
    }

    public function testReturnsTheContextOfTheGlobalNamespaceWithoutAFileOrLine(): void
    {
        $this->assertNull(NameContexts::at(false, false)->getNamespace());
    }
}
