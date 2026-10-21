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

use function array_values;
use function assert;
use function long2ip;
use function max;
use function min;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Distribution\BernoulliDistribution;
use PHPUnit\Framework\PropertyBased\Distribution\UniformDistribution;

/**
 * Generates IPv4 addresses of a network in dotted decimal notation.
 *
 * Half of the addresses are from the special-purpose ranges that the network
 * overlaps, such as private networks, loopback, link-local, multicast, and
 * the networks for documentation, or are the address right before or right
 * after such a range, where a check whether an address is in the range errs.
 * Within a range, the addresses are biased towards its first and its last
 * address. The other addresses are from the whole network, biased towards
 * its network address, the simplest one, and its broadcast address.
 *
 * @template-extends AbstractGenerator<non-empty-string>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Ipv4AddressGenerator extends AbstractGenerator
{
    /**
     * The special-purpose ranges of IPv4 addresses, as their first and their
     * last address.
     *
     * @var list<array{int, int}>
     */
    private const array SPECIAL_RANGES = [
        // "this network"
        [0x00000000, 0x00FFFFFF],
        // private network
        [0x0A000000, 0x0AFFFFFF],
        // shared address space for carrier-grade NAT
        [0x64400000, 0x647FFFFF],
        // loopback
        [0x7F000000, 0x7FFFFFFF],
        // link-local
        [0xA9FE0000, 0xA9FEFFFF],
        // private network
        [0xAC100000, 0xAC1FFFFF],
        // IETF protocol assignments
        [0xC0000000, 0xC00000FF],
        // documentation (TEST-NET-1)
        [0xC0000200, 0xC00002FF],
        // 6to4 relay anycast
        [0xC0586300, 0xC05863FF],
        // private network
        [0xC0A80000, 0xC0A8FFFF],
        // benchmarking
        [0xC6120000, 0xC613FFFF],
        // documentation (TEST-NET-2)
        [0xC6336400, 0xC63364FF],
        // documentation (TEST-NET-3)
        [0xCB007100, 0xCB0071FF],
        // multicast
        [0xE0000000, 0xEFFFFFFF],
        // reserved
        [0xF0000000, 0xFFFFFFFE],
        // limited broadcast
        [0xFFFFFFFF, 0xFFFFFFFF],
    ];

    /**
     * The probability that an address is drawn from the special-purpose
     * ranges rather than from the whole network.
     */
    private const float PROBABILITY_OF_SPECIAL_ADDRESS = 0.5;
    private int $networkAddress;
    private IntegerGenerator $addresses;
    private BernoulliDistribution $special;
    private UniformDistribution $uniform;

    /**
     * The ranges that the special-purpose ranges and the addresses next to
     * them have in common with the network, as their first address and a
     * generator for the offsets of the addresses in the range.
     *
     * @var list<array{int, IntegerGenerator}>
     */
    private array $specialRanges;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param int        $networkAddress the first address of the network, as an integer
     * @param int<0, 32> $prefixLength
     */
    public function __construct(int $networkAddress, int $prefixLength)
    {
        $this->label = self::labelOf('ipv4Addresses', (string) $networkAddress, (string) $prefixLength);

        $lastAddress = $networkAddress + (1 << (32 - $prefixLength)) - 1;

        $this->networkAddress = $networkAddress;
        $this->addresses      = new IntegerGenerator(0, $lastAddress - $networkAddress);
        $this->special        = new BernoulliDistribution(self::PROBABILITY_OF_SPECIAL_ADDRESS);
        $this->uniform        = new UniformDistribution;

        $specialRanges = [];

        foreach (self::SPECIAL_RANGES as [$first, $last]) {
            foreach ([[$first, $last], [$first - 1, $first - 1], [$last + 1, $last + 1]] as [$rangeFirst, $rangeLast]) {
                $rangeFirst = max($rangeFirst, $networkAddress);
                $rangeLast  = min($rangeLast, $lastAddress);

                if ($rangeFirst > $rangeLast) {
                    continue;
                }

                // an address next to one range can be another range, which is kept once
                $specialRanges[$rangeFirst . '-' . $rangeLast] = [$rangeFirst, new IntegerGenerator(0, $rangeLast - $rangeFirst)];
            }
        }

        $this->specialRanges = array_values($specialRanges);
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): string
    {
        if ($this->specialRanges !== [] && $source->draw(1, $this->special) === 1) {
            [$first, $offsets] = $source->choose($this->specialRanges, $this->uniform);

            return self::format($first + $offsets->generate($source));
        }

        return self::format($this->networkAddress + $this->addresses->generate($source));
    }

    /**
     * @return non-empty-string
     */
    private static function format(int $address): string
    {
        $formatted = long2ip($address);

        assert($formatted !== '');

        return $formatted;
    }
}
