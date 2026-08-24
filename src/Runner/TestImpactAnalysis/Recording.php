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

use function array_flip;
use function assert;
use function sprintf;

/**
 * What an earlier test run recorded, asked the questions that deciding which
 * tests to run asks of it.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @phpstan-type VersionType array{0: int, 1: non-empty-string}
 */
final readonly class Recording
{
    /**
     * @var list<non-empty-string>
     */
    private array $files;

    /**
     * @var list<VersionType>
     */
    private array $versions;

    /**
     * @var array<non-empty-string, list<int>>
     */
    private array $tests;

    /**
     * @var array<int, non-empty-string>
     */
    private array $sourceFiles;

    /**
     * @param list<non-empty-string>             $files
     * @param list<VersionType>                  $versions
     * @param array<non-empty-string, list<int>> $tests
     * @param array<int, non-empty-string>       $sourceFiles
     */
    public static function from(array $files, array $versions, array $tests, array $sourceFiles): self
    {
        return new self($files, $versions, $tests, $sourceFiles);
    }

    /**
     * @param list<non-empty-string>             $files
     * @param list<VersionType>                  $versions
     * @param array<non-empty-string, list<int>> $tests
     * @param array<int, non-empty-string>       $sourceFiles
     */
    private function __construct(array $files, array $versions, array $tests, array $sourceFiles)
    {
        $this->files       = $files;
        $this->versions    = $versions;
        $this->tests       = $tests;
        $this->sourceFiles = $sourceFiles;
    }

    public function isEmpty(): bool
    {
        return $this->tests === [];
    }

    /**
     * @param non-empty-string $test
     */
    public function knows(string $test): bool
    {
        return isset($this->tests[$test]);
    }

    /**
     * The tests that executed, or that declared that they depend on, something
     * that is not what it was when they were recorded.
     *
     * @return array<non-empty-string, true>
     */
    public function testsAffectedByWhatChanged(PathHasher $hasher): array
    {
        $affected = [];

        foreach ($this->tests as $test => $versionsOfTest) {
            foreach ($versionsOfTest as $version) {
                assert(isset($this->versions[$version]));

                if ($this->isCurrent($this->versions[$version], $hasher)) {
                    continue;
                }

                $affected[$test] = true;

                break;
            }
        }

        return $affected;
    }

    /**
     * A change that nothing that was recorded accounts for, or null when there
     * is none.
     *
     * A source file that is not among the files that were recorded is a file
     * that was not there, or was not first-party code, when the recording was
     * made. A source file that changed and that no test refers to is a file
     * that nothing is known about: that no test executed it does not mean that
     * no test is affected by it, only that executing the tests did not show it.
     *
     * @param list<non-empty-string> $sourceFiles the files that are subject to code coverage analysis
     *
     * @return ?non-empty-string the reason why nothing is known about it
     */
    public function changeNothingIsKnownAbout(PathHasher $hasher, array $sourceFiles): ?string
    {
        $recorded = array_flip($this->files);

        foreach ($sourceFiles as $sourceFile) {
            if (!isset($recorded[$sourceFile])) {
                return sprintf(
                    '%s was not there, or was not first-party code, when what is known was recorded',
                    $sourceFile,
                );
            }
        }

        $filesTestsRefersTo = [];

        foreach ($this->tests as $versionsOfTest) {
            foreach ($versionsOfTest as $version) {
                assert(isset($this->versions[$version]));

                $filesTestsRefersTo[$this->versions[$version][0]] = true;
            }
        }

        foreach ($this->sourceFiles as $position => $hash) {
            if (isset($filesTestsRefersTo[$position])) {
                continue;
            }

            assert(isset($this->files[$position]));

            if ($hasher->hash($this->files[$position]) === $hash) {
                continue;
            }

            return sprintf(
                '%s changed and no test is recorded as depending on it',
                $this->files[$position],
            );
        }

        return null;
    }

    /**
     * @param VersionType $version
     */
    private function isCurrent(array $version, PathHasher $hasher): bool
    {
        assert(isset($this->files[$version[0]]));

        return $hasher->hash($this->files[$version[0]]) === $version[1];
    }
}
