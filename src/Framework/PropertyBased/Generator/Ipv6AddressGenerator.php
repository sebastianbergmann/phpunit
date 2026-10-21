<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use function array_map;
use function array_slice;
use function assert;
use function dechex;
use function implode;
use function long2ip;
use function max;
use function min;
use function ord;
use function range;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Distribution\BernoulliDistribution;
use PHPUnit\Framework\PropertyBased\Distribution\UniformDistribution;

/**
 * Generates IPv6 addresses of a network in the canonical text representation
 * of RFC 5952: lowercase hexadecimal digits without leading zeros, the first
 * longest run of at least two groups of zeros compressed to "::", and the
 * last 32 bits of an IPv4-mapped address in dotted decimal notation.
 *
 * Half of the addresses are from the special-purpose ranges in the network,
 * such as the unspecified address, loopback, IPv4-mapped
 * addresses, the network for documentation, unique local addresses,
 * link-local addresses, and multicast. Half of the addresses have a run of
 * groups whose host bits are zero, of any length and at any position, where
 * the compression to "::" errs. The groups are biased towards all zeros and
 * all ones in their host bits.
 *
 * @template-extends AbstractGenerator<non-empty-string>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Ipv6AddressGenerator extends AbstractGenerator
{
    /**
     * The special-purpose ranges of IPv6 addresses, as the groups of their
     * first address and the length of their prefix.
     *
     * @var list<array{list<int>, int<0, 128>}>
     */
    private const array SPECIAL_RANGES = [
        // unspecified address
        [[0, 0, 0, 0, 0, 0, 0, 0], 128],
        // loopback
        [[0, 0, 0, 0, 0, 0, 0, 1], 128],
        // IPv4-mapped addresses
        [[0, 0, 0, 0, 0, 0xFFFF, 0, 0], 96],
        // IPv4/IPv6 translation
        [[0x64, 0xFF9B, 0, 0, 0, 0, 0, 0], 96],
        // documentation
        [[0x2001, 0xDB8, 0, 0, 0, 0, 0, 0], 32],
        // unique local addresses
        [[0xFC00, 0, 0, 0, 0, 0, 0, 0], 7],
        // link-local
        [[0xFE80, 0, 0, 0, 0, 0, 0, 0], 10],
        // multicast
        [[0xFF00, 0, 0, 0, 0, 0, 0, 0], 8],
    ];

    /**
     * The probability that an address is drawn from the special-purpose
     * ranges rather than from the whole network, and the probability that an
     * address has a run of groups whose host bits are zero.
     */
    private const float PROBABILITY_OF_SPECIAL_ADDRESS = 0.5;

    private const float PROBABILITY_OF_RUN_OF_ZEROS = 0.5;

    /**
     * The groups of the network, each with its network bits and a generator
     * for its host bits, or null when it has no host bits.
     *
     * @var list<array{int, ?IntegerGenerator}>
     */
    private array $network;

    /**
     * The special-purpose ranges in the network, as their groups like those
     * of the network.
     *
     * @var list<list<array{int, ?IntegerGenerator}>>
     */
    private array $specialRanges;
    private BernoulliDistribution $special;
    private BernoulliDistribution $runOfZeros;
    private UniformDistribution $uniform;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * The eight groups of 16 bits of an address, given as its 16 bytes.
     *
     * @return list<int>
     */
    public static function groupsOf(string $bytes): array
    {
        $groups = [];

        for ($index = 0; $index < 16; $index += 2) {
            $groups[] = (ord($bytes[$index]) << 8) | ord($bytes[$index + 1]);
        }

        return $groups;
    }

    /**
     * The groups of an address with only zeros in the bits after the prefix.
     *
     * @param list<int>   $groups
     * @param int<0, 128> $prefixLength
     *
     * @return list<int>
     */
    public static function withoutHostBits(array $groups, int $prefixLength): array
    {
        $masked = [];

        foreach ($groups as $index => $group) {
            $masked[] = $group & ~((1 << self::numberOfHostBits($index, $prefixLength)) - 1);
        }

        return $masked;
    }

    /**
     * The canonical text representation (RFC 5952) of the address with the
     * groups.
     *
     * @param list<int> $groups
     *
     * @return non-empty-string
     */
    public static function format(array $groups): string
    {
        if (array_slice($groups, 0, 6) === [0, 0, 0, 0, 0, 0xFFFF]) {
            $ipv4Address = 0;

            foreach (array_slice($groups, 6) as $group) {
                $ipv4Address = ($ipv4Address << 16) | $group;
            }

            return '::ffff:' . long2ip($ipv4Address);
        }

        // a single group of zeros is not compressed
        $longestRunStart  = null;
        $longestRunLength = 1;
        $runStart         = 0;
        $runLength        = 0;

        foreach ($groups as $index => $group) {
            if ($group !== 0) {
                $runLength = 0;

                continue;
            }

            if ($runLength === 0) {
                $runStart = $index;
            }

            $runLength++;

            // of runs that are equally long, the first one is compressed
            if ($runLength > $longestRunLength) {
                $longestRunStart  = $runStart;
                $longestRunLength = $runLength;
            }
        }

        $hexadecimal = array_map(dechex(...), $groups);

        if ($longestRunStart !== null) {
            return implode(':', array_slice($hexadecimal, 0, $longestRunStart)) . '::' . implode(':', array_slice($hexadecimal, $longestRunStart + $longestRunLength));
        }

        $formatted = implode(':', $hexadecimal);

        // an address has eight groups
        assert($formatted !== '');

        return $formatted;
    }

    /**
     * @param list<int>   $groups       the groups of the first address of the network
     * @param int<0, 128> $prefixLength
     */
    public function __construct(array $groups, int $prefixLength)
    {
        $this->label = self::labelOf('ipv6Addresses', self::format($groups), (string) $prefixLength);

        $this->network    = self::range($groups, $prefixLength);
        $this->special    = new BernoulliDistribution(self::PROBABILITY_OF_SPECIAL_ADDRESS);
        $this->runOfZeros = new BernoulliDistribution(self::PROBABILITY_OF_RUN_OF_ZEROS);
        $this->uniform    = new UniformDistribution;

        $specialRanges = [];

        foreach (self::SPECIAL_RANGES as [$specialGroups, $specialPrefixLength]) {
            // a range that contains the whole network is not special in it
            if ($specialPrefixLength <= $prefixLength) {
                continue;
            }

            // the network does not contain the range when they differ in the
            // bits of the prefix of the network
            if (self::withoutHostBits($specialGroups, $prefixLength) !== self::withoutHostBits($groups, $prefixLength)) {
                continue;
            }

            $specialRanges[] = self::range($specialGroups, $specialPrefixLength);
        }

        $this->specialRanges = $specialRanges;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): string
    {
        $range = $this->network;

        if ($this->specialRanges !== [] && $source->draw(1, $this->special) === 1) {
            $range = $source->choose($this->specialRanges, $this->uniform);
        }

        // no run of zeros starts after the last group
        $runStart = 8;
        $runEnd   = 8;

        if ($source->draw(1, $this->runOfZeros) === 1) {
            $runStart = $source->choose(range(0, 7), $this->uniform);
            $runEnd   = $runStart + 1 + $source->draw(7 - $runStart, $this->uniform);
        }

        $groups = [];

        foreach ($range as $index => [$group, $hostBits]) {
            if ($hostBits !== null && ($index < $runStart || $index >= $runEnd)) {
                $group |= $hostBits->generate($source);
            }

            $groups[] = $group;
        }

        return self::format($groups);
    }

    /**
     * @param int<0, 128> $prefixLength
     *
     * @return int<0, 16>
     */
    private static function numberOfHostBits(int $index, int $prefixLength): int
    {
        return max(0, min(16, 16 * ($index + 1) - $prefixLength));
    }

    /**
     * @param list<int>   $groups
     * @param int<0, 128> $prefixLength
     *
     * @return list<array{int, ?IntegerGenerator}>
     */
    private static function range(array $groups, int $prefixLength): array
    {
        $range = [];

        foreach (self::withoutHostBits($groups, $prefixLength) as $index => $group) {
            $numberOfHostBits = self::numberOfHostBits($index, $prefixLength);
            $hostBits         = null;

            if ($numberOfHostBits > 0) {
                $hostBits = new IntegerGenerator(0, (1 << $numberOfHostBits) - 1);
            }

            $range[] = [$group, $hostBits];
        }

        return $range;
    }
}
