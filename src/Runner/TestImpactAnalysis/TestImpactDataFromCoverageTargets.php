<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use function array_keys;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Api\CodeCoverage as CodeCoverageMetadataApi;
use PHPUnit\Metadata\Api\Fixtures;
use PHPUnit\Runner\TestIndex\TestFiles;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\StaticAnalysis\Registry;
use SebastianBergmann\CodeCoverage\Test\Target\InvalidCodeCoverageTargetException;
use SebastianBergmann\CodeCoverage\Test\Target\MapBuilder;
use SebastianBergmann\CodeCoverage\Test\Target\Mapper;
use SebastianBergmann\CodeCoverage\Test\Target\TargetCollection;

/**
 * What each test depends on, worked out from the code coverage targets it
 * declares instead of from what it was observed to execute.
 *
 * This needs no code coverage driver, and does not need the tests to be run.
 * It is only as good as the declarations, though, and the declarations are
 * only checked while code coverage is collected: a project that does not make
 * a test risky for executing code it does not declare has no reason to expect
 * the declarations to be complete.
 *
 * The targets a test declares that it uses count for as much as the ones it
 * declares that it covers: a change to code a test merely uses can change what
 * that test does just as well.
 *
 * A test that declares that it covers nothing names no source file and is not
 * recorded at all. Recording it without any source file would say that no
 * change can affect it, whereas not recording it leaves a test nothing is
 * known about, and a test nothing is known about is a test that has to be run.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestImpactDataFromCoverageTargets
{
    /**
     * @phpstan-ignore property.internalClass
     */
    private readonly Mapper $mapper;
    private readonly CodeCoverageMetadataApi $metadata;
    private readonly Fixtures $fixtures;

    /**
     * @param ?non-empty-string $staticAnalysisCacheDirectory
     */
    public static function using(Filter $filter, ?string $staticAnalysisCacheDirectory, bool $useAnnotationsForIgnoringCode, bool $ignoreDeprecatedCode): self
    {
        /** @phpstan-ignore staticMethod.internalClass */
        $analyser = Registry::analyser($staticAnalysisCacheDirectory, $useAnnotationsForIgnoringCode, $ignoreDeprecatedCode);

        /** @phpstan-ignore new.internalClass, method.internalClass */
        $map = (new MapBuilder)->build($filter, $analyser);

        /** @phpstan-ignore new.internalClass, method.internalClass */
        return new self(new Mapper($map));
    }

    /**
     * @phpstan-ignore parameter.internalClass
     */
    private function __construct(Mapper $mapper)
    {
        $this->mapper   = $mapper;
        $this->metadata = new CodeCoverageMetadataApi;
        $this->fixtures = new Fixtures;
    }

    /**
     * Tests that are not test methods, PHPT tests for instance, declare no code
     * coverage targets and are therefore not recorded.
     *
     * @param iterable<mixed> $tests
     */
    public function record(iterable $tests, TestImpactData $data): void
    {
        foreach ($tests as $test) {
            if (!$test instanceof TestCase) {
                continue;
            }

            $files = $this->filesFor($test::class, $test->name());

            /*
             * A test that names no source file is a test nothing is known
             * about, and the fixtures it declares do not change that: they add
             * to what a test depends on, they do not establish it. A test that
             * declares a target that cannot be resolved is a test nothing is
             * known about as well: what it names cannot be answered for, and
             * answering for the rest would say that it depends on less than it
             * does.
             */
            if ($files === null || $files === []) {
                continue;
            }

            foreach ($this->fixtures->for($test::class, $test->name()) as $fixture) {
                $files[] = $fixture;
            }

            $filesOfTest = TestFiles::of(new ReflectionClass($test));

            /*
             * A test that changed is a test that has to be run, and the files
             * the test itself is made of are what a change to it is seen in.
             * They are recorded here, and not looked up in the test index
             * later, because the index is written at a different moment: what
             * is recorded has to describe the code as it was when it was
             * recorded.
             */
            if ($filesOfTest === null) {
                continue;
            }

            foreach ($filesOfTest as $file) {
                $files[] = $file;
            }

            $data->record($test->valueObjectForEvents()->id(), $files);
        }
    }

    /**
     * Returns null when a target cannot be resolved to the files it stands
     * for: a target that names code that is not there, or that is not first-
     * party code, is a target no answer can be given for.
     *
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return ?list<non-empty-string>
     */
    private function filesFor(string $className, string $methodName): ?array
    {
        $files = [];

        foreach ([$this->metadata->coversTargets($className, $methodName), $this->metadata->usesTargets($className, $methodName)] as $targets) {
            $files = $this->addFilesOf($targets, $files);

            if ($files === null) {
                return null;
            }
        }

        return array_keys($files);
    }

    /**
     * @param array<non-empty-string, true> $files
     *
     * @return ?array<non-empty-string, true>
     */
    private function addFilesOf(TargetCollection $targets, array $files): ?array
    {
        if ($targets->isEmpty()) {
            return $files;
        }

        try {
            /** @phpstan-ignore method.internalClass */
            $mapped = $this->mapper->mapTargets($targets);
        } catch (InvalidCodeCoverageTargetException) {
            return null;
        }

        foreach (array_keys($mapped) as $file) {
            $files[$file] = true;
        }

        return $files;
    }
}
