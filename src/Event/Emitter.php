<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use PHPUnit\Framework\Constraint;
use SebastianBergmann\GlobalState\Snapshot;

interface Emitter
{
    public function applicationConfigured(): void;

    public function applicationStarted(): void;

    /**
     * @param mixed $value
     */
    public function assertionMade($value, Constraint\Constraint $constraint, string $message, bool $hasFailed): void;

    public function bootstrapFinished(string $filename): void;

    /**
     * @param class-string $className
     */
    public function comparatorRegistered(string $className): void;

    public function extensionLoaded(string $name, string $version): void;

    public function globalStateCaptured(Snapshot $snapshot): void;

    public function globalStateModified(Snapshot $snapshotBefore, Snapshot $snapshotAfter, string $message): void;

    public function globalStateRestored(Snapshot $snapshot): void;

    public function testRunConfigured(): void;

    public function testRunErrored(): void;

    public function testRunFailed(): void;

    public function testRunFinished(): void;

    public function testRunPassed(): void;

    public function testRunRisky(): void;

    public function testRunSkippedByDataProvider(): void;

    public function testRunSkippedIncomplete(): void;

    public function testRunSkippedWithFailedRequirements(): void;

    public function testRunSkippedWithWarning(): void;

    public function testRunStarted(): void;

    public function testSetUpFinished(): void;

    public function testTearDownFinished(): void;

    public function testCaseAfterClassFinished(): void;

    public function testCaseBeforeClassCalled(): void;

    public function testCaseBeforeClassFinished(): void;

    public function testCaseSetUpBeforeClassFinished(): void;

    public function testCaseSetUpFinished(): void;

    public function testCaseTearDownAfterClassFinished(): void;

    /**
     * @param class-string $className
     */
    public function testDoubleMockCreated(string $className): void;

    /**
     * @param trait-string $traitName
     */
    public function testDoubleMockForTraitCreated(string $traitName): void;

    /**
     * @param class-string $className
     */
    public function testDoublePartialMockCreated(string $className, string ...$methodNames): void;

    /**
     * @param class-string $className
     */
    public function testDoubleTestProxyCreated(string $className, array $constructorArguments): void;

    public function testSuiteAfterClassFinished(): void;

    public function testSuiteBeforeClassFinished(): void;

    public function testSuiteConfigured(): void;

    public function testSuiteLoaded(): void;

    public function testSuiteRunFinished(): void;

    public function testSuiteRunStarted(): void;

    public function testSuiteSorted(): void;
}
