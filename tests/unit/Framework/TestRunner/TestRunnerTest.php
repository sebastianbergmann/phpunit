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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestRunner\TestRunner;
use PHPUnit\TestFixture\CoverageMetadata\TestWithCoversClass;
use PHPUnit\TestFixture\CoverageMetadata\TestWithCoversClassesThatExtendClass;
use PHPUnit\TestFixture\CoverageMetadata\TestWithCoversClassesThatImplementInterface;
use PHPUnit\TestFixture\CoverageMetadata\TestWithCoversFunction;
use PHPUnit\TestFixture\CoverageMetadata\TestWithCoversMethod;
use PHPUnit\TestFixture\CoverageMetadata\TestWithCoversNamespace;
use PHPUnit\TestFixture\CoverageMetadata\TestWithCoversNothing;
use PHPUnit\TestFixture\CoverageMetadata\TestWithCoversTrait;
use PHPUnit\TestFixture\CoverageMetadata\TestWithoutCoverageMetadata;
use ReflectionMethod;

#[CoversClass(TestRunner::class)]
#[Small]
final class TestRunnerTest extends TestCase
{
    /**
     * @return non-empty-list<array{class-string}>
     */
    public static function provideTestWithCoverageMetadata(): array
    {
        return [
            [TestWithCoversNamespace::class],
            [TestWithCoversTrait::class],
            [TestWithCoversClass::class],
            [TestWithCoversClassesThatExtendClass::class],
            [TestWithCoversClassesThatImplementInterface::class],
            [TestWithCoversMethod::class],
            [TestWithCoversFunction::class],
            [TestWithCoversNothing::class],
        ];
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('provideTestWithCoverageMetadata')]
    #[TestDox('Recognizes $className as a test that defines a code coverage target')]
    public function testRecognizesTestThatDefinesCodeCoverageTarget(string $className): void
    {
        $this->assertTrue($this->hasCoverageMetadata($className));
    }

    public function testRecognizesTestThatDoesNotDefineCodeCoverageTarget(): void
    {
        $this->assertFalse($this->hasCoverageMetadata(TestWithoutCoverageMetadata::class));
    }

    /**
     * @param class-string $className
     */
    private function hasCoverageMetadata(string $className): bool
    {
        return new ReflectionMethod(TestRunner::class, 'hasCoverageMetadata')->invoke(
            new TestRunner,
            $className,
            'testOne',
        );
    }
}
