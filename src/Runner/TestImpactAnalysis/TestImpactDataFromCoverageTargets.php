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
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\StaticAnalysis\Registry;
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

            if ($files === []) {
                continue;
            }

            $data->record($test->valueObjectForEvents()->id(), $files);
        }
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return list<non-empty-string>
     */
    private function filesFor(string $className, string $methodName): array
    {
        $files = [];

        foreach ([$this->metadata->coversTargets($className, $methodName), $this->metadata->usesTargets($className, $methodName)] as $targets) {
            $files = $this->addFilesOf($targets, $files);
        }

        return array_keys($files);
    }

    /**
     * @param array<non-empty-string, true> $files
     *
     * @return array<non-empty-string, true>
     */
    private function addFilesOf(TargetCollection $targets, array $files): array
    {
        if ($targets->isEmpty()) {
            return $files;
        }

        /** @phpstan-ignore method.internalClass */
        foreach (array_keys($this->mapper->mapTargets($targets)) as $file) {
            $files[$file] = true;
        }

        return $files;
    }
}
