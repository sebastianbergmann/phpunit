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

use function hash_equals;
use function strlen;
use function substr;
use PHPUnit\Runner\CodeCoverage;
use SebastianBergmann\CodeCoverage\CodeCoverage as CodeCoverageData;

/**
 * The mechanics shared by the two consumers of the serialized result envelope
 * that a process-isolated child or a parallel worker writes back to the parent:
 * the ChildProcessResultProcessor and the parallel ResultAggregator.
 *
 * Only the parts that are identical between the two live here. The shape each
 * one requires of the decoded envelope, and the way each reports a malformed
 * result, differ and stay with each consumer.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ChildProcessResultEnvelope
{
    /**
     * Verify the nonce that prefixes the serialized result and strip it,
     * returning the bare payload.
     *
     * A null nonce or an empty payload is passed through unverified — the caller
     * then fails on the empty or unexpected payload when it tries to decode it.
     * Null is returned only when a nonce was expected but the prefix does not
     * match it, which means the result was written by an unexpected process or
     * was tampered with.
     *
     * @param ?non-empty-string $nonce
     */
    public static function verifyAndStripNonce(string $serialized, ?string $nonce): ?string
    {
        if ($nonce === null || $serialized === '') {
            return $serialized;
        }

        $length = strlen($nonce);

        if (strlen($serialized) < $length ||
            !hash_equals($nonce, substr($serialized, 0, $length))) {
            return null;
        }

        return substr($serialized, $length);
    }

    /**
     * Merge the code coverage carried by a decoded result envelope into the
     * parent's coverage, when coverage collection is active and the envelope
     * actually carries it.
     */
    public static function mergeCodeCoverage(object $result, CodeCoverage $codeCoverage): void
    {
        if (!$codeCoverage->isActive()) {
            return;
        }

        // @codeCoverageIgnoreStart
        if (!isset($result->codeCoverage) || !$result->codeCoverage instanceof CodeCoverageData) {
            return;
        }

        CodeCoverage::instance()->codeCoverage()->merge($result->codeCoverage);
        // @codeCoverageIgnoreEnd
    }
}
