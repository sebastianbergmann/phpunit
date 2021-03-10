<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function get_class;
use function preg_match;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Annotation\DocBlock;
use PHPUnit\TestFixture\NumericGroupAnnotationTest;

/**
 * @small
 */
final class TestTest extends TestCase
{
    /**
     * @var string
     */
    private $fileRequirementsTest;

    public function requirementsWithInvalidVersionConstraintsThrowsExceptionProvider(): array
    {
        return [
            ['testVersionConstraintInvalidPhpConstraint'],
            ['testVersionConstraintInvalidPhpUnitConstraint'],
        ];
    }

    /**
     * @todo This test does not really test functionality of \PHPUnit\Util\Test
     */
    public function testGetProvidedDataRegEx(): void
    {
        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('class::method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\class::method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider namespace\namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\namespace\class::method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider メソッド', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('メソッド', $matches[1]);
    }

    public function testParseDependsAnnotation(): void
    {
        $this->assertEquals(
            [
                new ExecutionOrderDependency(get_class($this), 'Foo'),
                new ExecutionOrderDependency(get_class($this), 'ほげ'),
                new ExecutionOrderDependency('AnotherClass::Foo'),
            ],
            Test::getDependencies(get_class($this), 'methodForTestParseAnnotation')
        );
    }

    /**
     * @depends Foo
     * @depends ほげ
     * @depends AnotherClass::Foo
     *
     * @todo Remove fixture from test class
     */
    public function methodForTestParseAnnotation(): void
    {
    }

    public function testParseAnnotationThatIsOnlyOneLine(): void
    {
        $this->assertEquals(
            [new ExecutionOrderDependency(get_class($this), 'Bar')],
            Test::getDependencies(get_class($this), 'methodForTestParseAnnotationThatIsOnlyOneLine')
        );
    }

    /** @depends Bar */
    public function methodForTestParseAnnotationThatIsOnlyOneLine(): void
    {
        // TODO Remove fixture from test class
    }

    /**
     * @testdox Parse @ticket for $class::$method
     * @dataProvider getGroupsProvider
     */
    public function testGetGroupsFromTicketAnnotations(string $class, string $method, array $groups): void
    {
        $this->assertSame($groups, Test::groups($class, $method));
    }

    public function getGroupsProvider(): array
    {
        return [
            [
                NumericGroupAnnotationTest::class,
                'testTicketAnnotationSupportsNumericValue',
                ['t123456', '3502'],
            ],
            [
                NumericGroupAnnotationTest::class,
                'testGroupAnnotationSupportsNumericValue',
                ['t123456', '3502'],
            ],
        ];
    }
}
