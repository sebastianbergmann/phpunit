<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use function assert;
use function preg_match;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function substr;

/**
 * The value of the --filter CLI option, compiled to the regular expressions
 * used to select tests.
 *
 * This is the only place where the syntax of that option is parsed. The regular
 * expression that is matched against the name of a test, including the name of
 * its data set, and the regular expression that is matched against the name of
 * a test method alone are derived from the same parse so that they cannot
 * disagree.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class CompiledNameFilter
{
    /**
     * @var non-empty-string
     */
    private string $regularExpression;

    /**
     * @var ?non-empty-string
     */
    private ?string $methodNameRegularExpression;
    private ?int $dataSetMinimum;
    private ?int $dataSetMaximum;

    public static function from(string $filter): self
    {
        if (!self::isFilterSyntax($filter)) {
            assert($filter !== '');

            return new self($filter, null, null, null);
        }

        $methodNamePortion = null;
        $dataSetMinimum    = null;
        $dataSetMaximum    = null;

        // Handles:
        //  * testAssertEqualsSucceeds#4
        //  * testAssertEqualsSucceeds#4-8
        if (preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $filter, $matches) === 1) {
            $methodNamePortion = $matches[1];

            if (isset($matches[3]) && $matches[2] < $matches[3]) {
                $filter = sprintf(
                    '%s.*with data set #(\d+)$',
                    $matches[1],
                );

                $dataSetMinimum = (int) $matches[2];
                $dataSetMaximum = (int) $matches[3];
            } elseif ($matches[1] !== '') {
                $filter = sprintf(
                    '%s.*with data set #%s$',
                    $matches[1],
                    $matches[2],
                );
            }
        } // Handles:
        //  * testAssertEqualsSucceeds#named data set
        elseif (preg_match('/^(.*?)#(.+)$/', $filter, $matches) === 1) {
            $methodNamePortion = $matches[1];

            $filter = sprintf(
                '%s.*with data set "%s"$',
                $matches[1],
                $matches[2],
            );
        } // Handles:
        //  * testDetermineJsonError@JSON_ERROR_NONE
        //  * testDetermineJsonError@JSON.*
        elseif (preg_match('/^(.*?)@(.+)$/', $filter, $matches) === 1) {
            $methodNamePortion = $matches[1];

            $filter = sprintf(
                '%s.*with data set "%s"$',
                $matches[1],
                $matches[2],
            );
        }

        // Do NOT use preg_quote, to keep magic characters.
        $regularExpression = sprintf('{%s}i', $filter);

        if ($methodNamePortion === null || $methodNamePortion === '') {
            $methodNameRegularExpression = null;
        } else {
            $methodNameRegularExpression = sprintf('{%s}i', $methodNamePortion);
        }

        return new self($regularExpression, $methodNameRegularExpression, $dataSetMinimum, $dataSetMaximum);
    }

    /**
     * @param non-empty-string  $regularExpression
     * @param ?non-empty-string $methodNameRegularExpression
     */
    private function __construct(string $regularExpression, ?string $methodNameRegularExpression, ?int $dataSetMinimum, ?int $dataSetMaximum)
    {
        $this->regularExpression           = $regularExpression;
        $this->methodNameRegularExpression = $methodNameRegularExpression;
        $this->dataSetMinimum              = $dataSetMinimum;
        $this->dataSetMaximum              = $dataSetMaximum;
    }

    /**
     * The regular expression to match against the name of a test, including the
     * name of its data set.
     *
     * @return non-empty-string
     */
    public function regularExpression(): string
    {
        return $this->regularExpression;
    }

    /**
     * @phpstan-assert-if-true !null $this->methodNameRegularExpression
     */
    public function constrainsMethodName(): bool
    {
        return $this->methodNameRegularExpression !== null;
    }

    /**
     * The regular expression to match against the name of a test method, not
     * including the name of a data set.
     *
     * This is only available when the filter has a data set portion. A filter
     * without one is matched against the entire name of a test, including the
     * name of its data set, and may therefore match a data set name that cannot
     * be known without invoking the data provider (#6741).
     *
     * @return non-empty-string
     */
    public function methodNameRegularExpression(): string
    {
        assert($this->methodNameRegularExpression !== null);

        return $this->methodNameRegularExpression;
    }

    /**
     * @phpstan-assert-if-true !null $this->dataSetMinimum
     * @phpstan-assert-if-true !null $this->dataSetMaximum
     */
    public function hasDataSetRange(): bool
    {
        return $this->dataSetMaximum !== null;
    }

    public function dataSetMinimum(): int
    {
        assert($this->dataSetMinimum !== null);

        return $this->dataSetMinimum;
    }

    public function dataSetMaximum(): int
    {
        assert($this->dataSetMaximum !== null);

        return $this->dataSetMaximum;
    }

    /**
     * A filter that does not begin with an alphanumeric character and that is a
     * valid regular expression is used as-is instead of being parsed.
     */
    private static function isFilterSyntax(string $filter): bool
    {
        if (preg_match('/[a-zA-Z0-9]/', substr($filter, 0, 1)) === 1) {
            return true;
        }

        return !self::isRegularExpression($filter);
    }

    private static function isRegularExpression(string $filter): bool
    {
        set_error_handler(static fn (): bool => true);

        try {
            return preg_match($filter, '') !== false;
        } finally {
            restore_error_handler();
        }
    }
}
