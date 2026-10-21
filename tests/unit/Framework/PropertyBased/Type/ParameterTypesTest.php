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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\InvalidTypeException;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParameterTypes::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ParameterTypesTest extends TestCase
{
    public function testReadsTheTypesOfTheParametersFromTheirTags(): void
    {
        $types = ParameterTypes::fromDocComment(
            <<<'EOT'
/**
 * Greets the person, who's identified by their name.
 *
 * @param non-empty-string $name the person's name
 * @param int<0, 150>      $age
 *
 * @return void
 */
EOT,
        );

        $this->assertTrue($types->has('name'));
        $this->assertSame('non-empty-string', $types->typeOf('name')?->asString());
        $this->assertSame('int<0, 150>', $types->typeOf('age')?->asString());
        $this->assertFalse($types->has('unknown'));
        $this->assertNull($types->typeOf('unknown'));
    }

    public function testReadsATypeThatSpansSeveralLines(): void
    {
        $types = ParameterTypes::fromDocComment(
            <<<'EOT'
/**
 * @param array{
 *     name: non-empty-string,
 *     age?: int<0, 150>,
 * } $person
 */
EOT,
        );

        $this->assertSame('array{name: non-empty-string, age?: int<0, 150>}', $types->typeOf('person')?->asString());
    }

    public function testReadsTheTypeOfAParameterFromASingleLineDocComment(): void
    {
        $this->assertSame('list<int>', ParameterTypes::fromDocComment('/** @param list<int> $values */')->typeOf('values')?->asString());
    }

    public function testReadsTheTypesOfParametersThatArePassedByReferenceOrAreVariadic(): void
    {
        $types = ParameterTypes::fromDocComment(
            <<<'EOT'
/**
 * @param int    &$reference
 * @param string ...$variadic
 * @param bool   &...$both
 */
EOT,
        );

        $this->assertSame('int', $types->typeOf('reference')?->asString());
        $this->assertSame('string', $types->typeOf('variadic')?->asString());
        $this->assertSame('bool', $types->typeOf('both')?->asString());
    }

    public function testThePhpstanParamTagTakesPrecedenceOverThePsalmParamTagWhichTakesPrecedenceOverTheParamTag(): void
    {
        $types = ParameterTypes::fromDocComment(
            <<<'EOT'
/**
 * @param string $a
 * @param string $b
 * @param string $c
 * @psalm-param non-empty-string $a
 * @psalm-param non-empty-string $b
 * @phpstan-param non-falsy-string $a
 */
EOT,
        );

        $this->assertSame('non-falsy-string', $types->typeOf('a')?->asString());
        $this->assertSame('non-empty-string', $types->typeOf('b')?->asString());
        $this->assertSame('string', $types->typeOf('c')?->asString());
    }

    public function testTheFirstOfSeveralTagsOfTheSameKindForAParameterIsUsed(): void
    {
        $types = ParameterTypes::fromDocComment(
            <<<'EOT'
/**
 * @param int    $a
 * @param string $a
 */
EOT,
        );

        $this->assertSame('int', $types->typeOf('a')?->asString());
    }

    public function testATagWithoutATypeDoesNotDeclareTheTypeOfItsParameter(): void
    {
        $this->assertFalse(ParameterTypes::fromDocComment('/** @param $a the description */')->has('a'));
    }

    public function testATagWithoutAParameterIsIgnored(): void
    {
        $this->assertFalse(ParameterTypes::fromDocComment('/** @param int */')->has('a'));
    }

    public function testOtherTagsThatStartWithParamAreIgnored(): void
    {
        $this->assertFalse(ParameterTypes::fromDocComment('/** @param-out int $a */')->has('a'));
    }

    public function testATypeThatCannotBeParsedIsReportedWhenTheTypeOfItsParameterIsRequested(): void
    {
        $types = ParameterTypes::fromDocComment(
            <<<'EOT'
/**
 * @param callable(int): string $callback
 * @param int                   $a
 */
EOT,
        );

        $this->assertTrue($types->has('callback'));
        $this->assertSame('int', $types->typeOf('a')?->asString());

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Unexpected "(" at offset 9, expected the name of the parameter');

        $types->typeOf('callback');
    }

    public function testATagWhoseTypeCannotBeParsedAndThatHasNoParameterIsIgnored(): void
    {
        $this->assertFalse(ParameterTypes::fromDocComment('/** @param list<int */')->has('a'));
    }

    public function testAMethodWithoutADocCommentDoesNotDeclareTheTypesOfItsParameters(): void
    {
        $this->assertFalse(ParameterTypes::fromDocComment(false)->has('a'));
    }
}
