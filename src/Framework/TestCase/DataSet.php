<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use function is_int;
use function sprintf;
use PHPUnit\Util\Exporter;
use PHPUnit\Util\Sanitizer;

/**
 * The data set that a data provider provided for a test method.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DataSet
{
    private int|string $name;

    /**
     * @var array<mixed>
     */
    private array $data;

    public static function empty(): self
    {
        return new self('', []);
    }

    /**
     * @param array<mixed> $data
     */
    public function __construct(int|string $name, array $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function isEmpty(): bool
    {
        return $this->data === [];
    }

    public function name(): int|string
    {
        return $this->name;
    }

    /**
     * @return array<mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    public function asString(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        if (is_int($this->name)) {
            return sprintf(' with data set #%s', $this->name);
        }

        return sprintf(
            ' with data set "%s"',
            Sanitizer::sanitizeBidirectionalControlCharacters($this->name),
        );
    }

    public function asStringWithData(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        if (is_int($this->name)) {
            $name = sprintf('#%d', $this->name);
        } else {
            $name = sprintf(
                '@%s',
                Sanitizer::sanitizeBidirectionalControlCharacters($this->name),
            );
        }

        return sprintf(
            '%s with data (%s)',
            $name,
            Exporter::shortenedRecursiveExport($this->data),
        );
    }
}
