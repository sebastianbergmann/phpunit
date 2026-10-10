<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

use function sprintf;

/**
 * Decides whether a test is risky for a reason that concerns the test as a
 * whole: for the assertions that it performed or for its contribution to
 * code coverage. For a property, these are decided on all of its examples.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RiskyTestChecks
{
    private bool $reportUselessTests;

    public function __construct(bool $reportUselessTests)
    {
        $this->reportUselessTests = $reportUselessTests;
    }

    /**
     * Returns why a test that performed no assertions is risky, or null when
     * it performed assertions, is not expected to perform any, or useless
     * tests are not reported.
     *
     * @param non-negative-int $numberOfAssertions
     *
     * @return ?non-empty-string
     */
    public function performedNoAssertions(int $numberOfAssertions, bool $doesNotPerformAssertions): ?string
    {
        if (!$this->reportUselessTests || $doesNotPerformAssertions || $numberOfAssertions > 0) {
            return null;
        }

        return 'This test did not perform any assertions';
    }

    /**
     * Returns why a test that is not expected to perform assertions is risky
     * because it performed some, or null when it is not.
     *
     * @param non-negative-int $numberOfAssertions
     *
     * @return ?non-empty-string
     */
    public function performedUnexpectedAssertions(int $numberOfAssertions, bool $doesNotPerformAssertions): ?string
    {
        if (!$doesNotPerformAssertions || $numberOfAssertions === 0) {
            return null;
        }

        $plural = '';

        if ($numberOfAssertions > 1) {
            $plural = 's';
        }

        return sprintf(
            'This test is not expected to perform assertions but performed %d assertion%s',
            $numberOfAssertions,
            $plural,
        );
    }

    /**
     * Returns why a test that did not contribute to code coverage is risky,
     * or null when it did or when this was not checked.
     *
     * @return ?non-empty-string
     */
    public function didNotContributeToCodeCoverage(?bool $contributedToCodeCoverage): ?string
    {
        if ($contributedToCodeCoverage !== false) {
            return null;
        }

        return 'This test does not contribute to code coverage';
    }
}
