<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Metadata\MetadataCollection;

#[CoversClass(ParserChain::class)]
#[Small]
#[Group('metadata')]
#[Group('metadata/annotations')]
final class ChainedAnnotationParserTest extends AnnotationParserTestCase
{
    protected function parser(): Parser
    {
        $attributeReader = $this->createStub(Parser::class);

        $attributeReader->method('forClassAndMethod')->willReturn(MetadataCollection::fromArray([]));
        $attributeReader->method('forClass')->willReturn(MetadataCollection::fromArray([]));
        $attributeReader->method('forMethod')->willReturn(MetadataCollection::fromArray([]));

        return new ParserChain(
            $attributeReader,
            new AnnotationParser,
        );
    }
}
