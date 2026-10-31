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

use function assert;
use function min;
use function strlen;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use SebastianBergmann\ChoiceSequence\BernoulliDistribution;
use SebastianBergmann\ChoiceSequence\BoundaryDistribution;
use SebastianBergmann\ChoiceSequence\UniformDistribution;

/**
 * Generates email addresses (RFC 5321) of at most 254 characters, with a
 * local part of at most 64 characters.
 *
 * The local part consists of the characters that RFC 5322 allows in an atom
 * and of dots, but neither starts nor ends with a dot nor has two dots in a
 * row. With $quotedLocalParts, a quarter of the local parts are quoted
 * strings of printable ASCII characters, including spaces, in which quotes
 * and backslashes are escaped with a backslash.
 *
 * The domain is a domain name. With $ipAddressLiterals, a quarter of the
 * domains are address literals of an IPv4 or an IPv6 address instead.
 *
 * The domain is drawn first, then the local part, so that it fits into what
 * the domain leaves. The lengths are biased towards their boundaries, so
 * that local parts of 64 characters and email addresses of 254 characters
 * are generated often.
 *
 * @template-extends AbstractGenerator<non-empty-string>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class EmailAddressGenerator extends AbstractGenerator
{
    private const int LONGEST_LENGTH       = 254;
    private const int LONGEST_LOCAL_PART   = 64;
    private const string ATOM_CHARACTERS   = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!#$%&\'*+-/=?^_`{|}~';
    private const string QUOTED_CHARACTERS = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ !#$%&\'()*+,-./:;<=>?@[]^_`{|}~';

    /**
     * The probability that a local part is a quoted string, with
     * $quotedLocalParts, and the probability that a domain is an address
     * literal, with $ipAddressLiterals.
     */
    private const float PROBABILITY_OF_QUOTED = 0.25;

    private const float PROBABILITY_OF_LITERAL = 0.25;
    private bool $quotedLocalParts;
    private bool $ipAddressLiterals;
    private DomainNameGenerator $domainNames;
    private Ipv4AddressGenerator $ipv4Addresses;
    private Ipv6AddressGenerator $ipv6Addresses;
    private BernoulliDistribution $quoted;
    private BernoulliDistribution $literal;
    private BoundaryDistribution $length;
    private UniformDistribution $uniform;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @var non-empty-string
     */
    private string $labelOfCharacters;

    public function __construct(bool $quotedLocalParts, bool $ipAddressLiterals)
    {
        $this->label             = self::labelOf('emailAddresses', (string) (int) $quotedLocalParts, (string) (int) $ipAddressLiterals);
        $this->labelOfCharacters = self::labelOf('character', $this->label);

        $this->quotedLocalParts  = $quotedLocalParts;
        $this->ipAddressLiterals = $ipAddressLiterals;

        // the shortest local part and the @ take two characters
        $this->domainNames   = new DomainNameGenerator(self::LONGEST_LENGTH - 2);
        $this->ipv4Addresses = new Ipv4AddressGenerator(0, 0);
        $this->ipv6Addresses = new Ipv6AddressGenerator([0, 0, 0, 0, 0, 0, 0, 0], 0);
        $this->quoted        = new BernoulliDistribution(self::PROBABILITY_OF_QUOTED);
        $this->literal       = new BernoulliDistribution(self::PROBABILITY_OF_LITERAL);
        $this->length        = new BoundaryDistribution;
        $this->uniform       = new UniformDistribution;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): string
    {
        $domain    = $this->domain($source);
        $remaining = self::LONGEST_LENGTH - 1 - strlen($domain);

        // the domain leaves room for the shortest local part
        assert($remaining > 0);

        return $this->localPart($source, min(self::LONGEST_LOCAL_PART, $remaining)) . '@' . $domain;
    }

    /**
     * @return non-empty-string
     */
    private function domain(ChoiceSource $source): string
    {
        if (!$this->ipAddressLiterals || $source->draw(1, $this->literal) === 0) {
            return $this->domainNames->generate($source);
        }

        if ($source->draw(1, $this->uniform) === 0) {
            return '[' . $this->ipv4Addresses->generate($source) . ']';
        }

        return '[IPv6:' . $this->ipv6Addresses->generate($source) . ']';
    }

    /**
     * @param positive-int $maximumLength
     *
     * @return non-empty-string
     */
    private function localPart(ChoiceSource $source, int $maximumLength): string
    {
        // a quoted string takes two characters more than its content
        if ($this->quotedLocalParts && $maximumLength >= 3 && $source->draw(1, $this->quoted) === 1) {
            return $this->quotedString($source, $maximumLength - 2);
        }

        return $this->dotAtom($source, $maximumLength);
    }

    /**
     * @param positive-int $maximumLength
     *
     * @return non-empty-string
     */
    private function dotAtom(ChoiceSource $source, int $maximumLength): string
    {
        $length  = 1 + $source->draw($maximumLength - 1, $this->length);
        $dotAtom = $this->character($source, self::ATOM_CHARACTERS);

        for ($index = 1; $index < $length; $index++) {
            // a dot is neither the last character nor next to another dot
            if ($index < $length - 1 && $dotAtom[$index - 1] !== '.') {
                $dotAtom .= $this->character($source, self::ATOM_CHARACTERS . '.');

                continue;
            }

            $dotAtom .= $this->character($source, self::ATOM_CHARACTERS);
        }

        return $dotAtom;
    }

    /**
     * @param positive-int $maximumLengthOfContent
     *
     * @return non-empty-string
     */
    private function quotedString(ChoiceSource $source, int $maximumLengthOfContent): string
    {
        $length  = 1 + $source->draw($maximumLengthOfContent - 1, $this->length);
        $content = '';

        while (strlen($content) < $length) {
            // the choices after the characters stand for an escaped quote
            // and an escaped backslash, which take two characters
            $maximum = strlen(self::QUOTED_CHARACTERS) - 1;

            if ($length - strlen($content) >= 2) {
                $maximum += 2;
            }

            $source->startSpan($this->labelOfCharacters);

            $choice = $source->draw($maximum, $this->uniform);

            $source->endSpan();

            if ($choice < strlen(self::QUOTED_CHARACTERS)) {
                $content .= self::QUOTED_CHARACTERS[$choice];
            } elseif ($choice === strlen(self::QUOTED_CHARACTERS)) {
                $content .= '\\"';
            } else {
                $content .= '\\\\';
            }
        }

        return '"' . $content . '"';
    }

    /**
     * Each character is a span, so that a local part is shortened by lowering
     * its length while its last characters are deleted.
     *
     * @param non-empty-string $alphabet
     *
     * @return non-empty-string
     */
    private function character(ChoiceSource $source, string $alphabet): string
    {
        $source->startSpan($this->labelOfCharacters);

        $character = $alphabet[$source->draw(strlen($alphabet) - 1, $this->uniform)];

        $source->endSpan();

        return $character;
    }
}
