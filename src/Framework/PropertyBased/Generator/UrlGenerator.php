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

use function array_merge;
use function implode;
use function str_split;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use SebastianBergmann\ChoiceSequence\BernoulliDistribution;
use SebastianBergmann\ChoiceSequence\UniformDistribution;

/**
 * Generates URLs (RFC 3986) with an authority: a scheme, optional user
 * information, a host, an optional port, a path, an optional query, and an
 * optional fragment.
 *
 * The host is a domain name, an IPv4 address, or an IPv6 address in
 * brackets. The port is biased towards 1, 65535, and common default ports.
 * The path consists of segments, of which the last one is empty for a
 * trailing slash, the query of key-value pairs, which are separated by "&".
 *
 * The URLs are normalized, as far as their syntax goes: the scheme and the
 * host are lowercase, characters are percent-encoded only when they have
 * to be, the hexadecimal digits of their encoding are uppercase, and no
 * segment is "." or "..". The characters that are percent-encoded include
 * reserved characters, spaces, and characters that are encoded with two,
 * three, and four bytes in UTF-8.
 *
 * @template-extends AbstractGenerator<non-empty-string>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class UrlGenerator extends AbstractGenerator
{
    private const string LETTERS_AND_DIGITS = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const string SUB_DELIMITERS     = '!$&\'()*+,;=';

    /**
     * The sub-delimiters, without the ones that separate the key-value pairs
     * of a query and their keys from their values ("&", "+", and "=").
     */
    private const string SUB_DELIMITERS_IN_QUERIES = '!$\'()*,;';

    /**
     * Characters that are percent-encoded, as their encoding: a space, the
     * characters that are neither reserved nor unreserved, the reserved
     * characters that delimit the parts of a URL or the key-value pairs of
     * a query, and characters that are encoded with two, three, and four
     * bytes in UTF-8 (ä, €, and 😀).
     */
    private const array PERCENT_ENCODED_CHARACTERS = [
        '%20', '%22', '%25', '%3C', '%3E', '%5C', '%5E', '%60', '%7B', '%7C', '%7D',
        '%23', '%26', '%2B', '%2F', '%3A', '%3D', '%3F', '%40', '%5B', '%5D',
        '%C3%A4', '%E2%82%AC', '%F0%9F%98%80',
    ];

    /**
     * Ports that are the default port of a common scheme.
     */
    private const array DEFAULT_PORTS = [80, 443, 21, 8080];

    /**
     * The probabilities that a URL has the optional parts, and the
     * probability that a port is a default port rather than any port.
     */
    private const float PROBABILITY_OF_USER_INFORMATION = 0.5;

    private const float PROBABILITY_OF_PASSWORD     = 0.5;
    private const float PROBABILITY_OF_IP_ADDRESS   = 0.4;
    private const float PROBABILITY_OF_PORT         = 0.3;
    private const float PROBABILITY_OF_DEFAULT_PORT = 0.25;
    private const float PROBABILITY_OF_QUERY        = 0.3;
    private const float PROBABILITY_OF_FRAGMENT     = 0.2;

    /**
     * @var non-empty-list<non-empty-string>
     */
    private array $schemes;
    private bool $userInformation;
    private DomainNameGenerator $domainNames;
    private Ipv4AddressGenerator $ipv4Addresses;
    private Ipv6AddressGenerator $ipv6Addresses;
    private IntegerGenerator $ports;
    private Repetition $segments;
    private Repetition $pairs;
    private Repetition $characters;
    private Repetition $atLeastOneCharacter;

    /**
     * @var non-empty-list<non-empty-string>
     */
    private array $charactersOfUserInformation;

    /**
     * @var non-empty-list<non-empty-string>
     */
    private array $charactersOfSegments;

    /**
     * @var non-empty-list<non-empty-string>
     */
    private array $firstCharactersOfSegments;

    /**
     * @var non-empty-list<non-empty-string>
     */
    private array $charactersOfQueries;

    /**
     * @var non-empty-list<non-empty-string>
     */
    private array $charactersOfFragments;
    private BernoulliDistribution $hasUserInformation;
    private BernoulliDistribution $hasPassword;
    private BernoulliDistribution $isIpAddress;
    private BernoulliDistribution $hasPort;
    private BernoulliDistribution $isDefaultPort;
    private BernoulliDistribution $hasQuery;
    private BernoulliDistribution $hasFragment;
    private UniformDistribution $uniform;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param non-empty-list<non-empty-string> $schemes         the schemes to choose from, the first one simplest
     * @param bool                             $userInformation whether URLs with user information are generated as well
     */
    public function __construct(array $schemes, bool $userInformation)
    {
        $this->label = self::labelOf('urls', implode(',', $schemes), (string) (int) $userInformation);

        $this->schemes             = $schemes;
        $this->userInformation     = $userInformation;
        $this->domainNames         = new DomainNameGenerator(DomainNameGenerator::LONGEST_LENGTH);
        $this->ipv4Addresses       = new Ipv4AddressGenerator(0, 0);
        $this->ipv6Addresses       = new Ipv6AddressGenerator([0, 0, 0, 0, 0, 0, 0, 0], 0);
        $this->ports               = new IntegerGenerator(1, 65535);
        $this->segments            = new Repetition(self::labelOf('segment', $this->label), 0, null);
        $this->pairs               = new Repetition(self::labelOf('pair', $this->label), 0, null);
        $this->characters          = new Repetition(self::labelOf('character', $this->label), 0, null);
        $this->atLeastOneCharacter = new Repetition(self::labelOf('character', $this->label), 1, null);

        $unreserved = str_split(self::LETTERS_AND_DIGITS . '-._~');

        $this->charactersOfUserInformation = array_merge($unreserved, str_split(self::SUB_DELIMITERS), self::PERCENT_ENCODED_CHARACTERS);
        $this->charactersOfSegments        = array_merge($this->charactersOfUserInformation, [':', '@']);
        $this->charactersOfQueries         = array_merge($unreserved, str_split(self::SUB_DELIMITERS_IN_QUERIES), [':', '@', '/', '?'], self::PERCENT_ENCODED_CHARACTERS);
        $this->charactersOfFragments       = array_merge($this->charactersOfSegments, ['/', '?']);

        // a segment that starts with a dot is neither "." nor "..", which
        // are removed when a URL is normalized
        $this->firstCharactersOfSegments = array_merge(str_split(self::LETTERS_AND_DIGITS . '-_~'), str_split(self::SUB_DELIMITERS), [':', '@'], self::PERCENT_ENCODED_CHARACTERS);

        $this->hasUserInformation = new BernoulliDistribution(self::PROBABILITY_OF_USER_INFORMATION);
        $this->hasPassword        = new BernoulliDistribution(self::PROBABILITY_OF_PASSWORD);
        $this->isIpAddress        = new BernoulliDistribution(self::PROBABILITY_OF_IP_ADDRESS);
        $this->hasPort            = new BernoulliDistribution(self::PROBABILITY_OF_PORT);
        $this->isDefaultPort      = new BernoulliDistribution(self::PROBABILITY_OF_DEFAULT_PORT);
        $this->hasQuery           = new BernoulliDistribution(self::PROBABILITY_OF_QUERY);
        $this->hasFragment        = new BernoulliDistribution(self::PROBABILITY_OF_FRAGMENT);
        $this->uniform            = new UniformDistribution;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): string
    {
        return $source->choose($this->schemes, $this->uniform) . '://' .
            $this->userInformation($source) .
            $this->host($source) .
            $this->port($source) .
            $this->path($source) .
            $this->query($source) .
            $this->fragment($source);
    }

    private function userInformation(ChoiceSource $source): string
    {
        if (!$this->userInformation || $source->draw(1, $this->hasUserInformation) === 0) {
            return '';
        }

        $userInformation = $this->string($source, $this->atLeastOneCharacter, $this->charactersOfUserInformation);

        if ($source->draw(1, $this->hasPassword) === 1) {
            $userInformation .= ':' . $this->string($source, $this->atLeastOneCharacter, $this->charactersOfUserInformation);
        }

        return $userInformation . '@';
    }

    /**
     * @return non-empty-string
     */
    private function host(ChoiceSource $source): string
    {
        if ($source->draw(1, $this->isIpAddress) === 0) {
            return $this->domainNames->generate($source);
        }

        if ($source->draw(1, $this->uniform) === 0) {
            return $this->ipv4Addresses->generate($source);
        }

        return '[' . $this->ipv6Addresses->generate($source) . ']';
    }

    private function port(ChoiceSource $source): string
    {
        if ($source->draw(1, $this->hasPort) === 0) {
            return '';
        }

        if ($source->draw(1, $this->isDefaultPort) === 1) {
            return ':' . $source->choose(self::DEFAULT_PORTS, $this->uniform);
        }

        return ':' . $this->ports->generate($source);
    }

    private function path(ChoiceSource $source): string
    {
        $path = '';

        $this->segments->repeat(
            $source,
            function () use ($source, &$path): bool
            {
                $segment = '';

                $this->characters->repeat(
                    $source,
                    function () use ($source, &$segment): bool
                    {
                        $characters = $this->charactersOfSegments;

                        if ($segment === '') {
                            $characters = $this->firstCharactersOfSegments;
                        }

                        $segment .= $source->choose($characters, $this->uniform);

                        return true;
                    },
                );

                $path .= '/' . $segment;

                return true;
            },
        );

        return $path;
    }

    private function query(ChoiceSource $source): string
    {
        if ($source->draw(1, $this->hasQuery) === 0) {
            return '';
        }

        $pairs = [];

        $this->pairs->repeat(
            $source,
            function () use ($source, &$pairs): bool
            {
                $pairs[] = $this->string($source, $this->atLeastOneCharacter, $this->charactersOfQueries) . '=' . $this->string($source, $this->characters, $this->charactersOfQueries);

                return true;
            },
        );

        return '?' . implode('&', $pairs);
    }

    private function fragment(ChoiceSource $source): string
    {
        if ($source->draw(1, $this->hasFragment) === 0) {
            return '';
        }

        return '#' . $this->string($source, $this->characters, $this->charactersOfFragments);
    }

    /**
     * @param non-empty-list<non-empty-string> $characters
     */
    private function string(ChoiceSource $source, Repetition $repetition, array $characters): string
    {
        $string = '';

        $repetition->repeat(
            $source,
            function () use ($source, $characters, &$string): bool
            {
                $string .= $source->choose($characters, $this->uniform);

                return true;
            },
        );

        return $string;
    }
}
