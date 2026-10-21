<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Type;

use function sprintf;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Token
{
    private TokenKind $kind;
    private string $text;
    private float|int|string $value;

    /**
     * @var non-negative-int
     */
    private int $offset;

    /**
     * @param string           $text   the token as it is written
     * @param non-negative-int $offset the offset of the token in the text that is lexed
     */
    public function __construct(TokenKind $kind, string $text, float|int|string $value, int $offset)
    {
        $this->kind   = $kind;
        $this->text   = $text;
        $this->value  = $value;
        $this->offset = $offset;
    }

    public function kind(): TokenKind
    {
        return $this->kind;
    }

    public function text(): string
    {
        return $this->text;
    }

    /**
     * The value of a literal, the name of an identifier or variable, or the
     * symbol.
     */
    public function value(): float|int|string
    {
        return $this->value;
    }

    /**
     * @return non-negative-int
     */
    public function offset(): int
    {
        return $this->offset;
    }

    public function isSymbol(string $symbol): bool
    {
        return $this->kind === TokenKind::Symbol && $this->text === $symbol;
    }

    /**
     * @return non-empty-string
     */
    public function describe(): string
    {
        if ($this->kind === TokenKind::End) {
            return 'end of the type';
        }

        return sprintf('"%s" at offset %d', $this->text, $this->offset);
    }
}
