<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Baseline;

use function array_shift;
use function assert;
use function count;
use function explode;
use function implode;
use function min;
use function range;
use function str_repeat;
use function str_replace;
use function str_starts_with;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class RelativePathCalculator
{
    private readonly string $baselineDirectory;

    public function __construct(string $baselineDirectory)
    {
        $this->baselineDirectory = $baselineDirectory;
    }

    /**
     * @psalm-param non-empty-string $baselineDirectory
     * @psalm-param non-empty-string $file
     *
     * @psalm-return non-empty-string
     */
    public function calculate(string $file): string
    {
        if (str_starts_with($file, $this->baselineDirectory . DIRECTORY_SEPARATOR)) {
            $result = str_replace($this->baselineDirectory . DIRECTORY_SEPARATOR, '', $file);

            assert(!empty($result));

            return $result;
        }

        $from   = explode(DIRECTORY_SEPARATOR, $this->baselineDirectory);
        $to     = explode(DIRECTORY_SEPARATOR, $file);
        $common = 0;

        foreach (range(1, min(count($from), count($to))) as $i) {
            if ($from[0] === $to[0]) {
                array_shift($from);
                array_shift($to);

                $common++;
            }
        }

        assert($common > 0);

        $result = str_repeat('..' . DIRECTORY_SEPARATOR, count($from)) . implode(DIRECTORY_SEPARATOR, $to);

        assert(!empty($result));

        return $result;
    }
}
