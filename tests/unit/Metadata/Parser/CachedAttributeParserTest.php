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

#[CoversClass(CachingParser::class)]
#[Small]
#[Group('metadata')]
#[Group('metadata/attributes')]
final class CachedAttributeParserTest extends AttributeParserTestCase
{
    protected function parser(): Parser
    {
        return new CachingParser(new AttributeParser);
    }
}
