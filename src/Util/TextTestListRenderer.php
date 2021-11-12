<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use const PHP_EOL;
use PHPUnit\Framework\TestSuite;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TextTestListRenderer
{
    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function render(TestSuite $suite): string
    {
        $buffer = 'Available test(s):';
        $buffer .= PHP_EOL . ' - ';
        $buffer .= implode(PHP_EOL . ' - ', $suite->getTestNameArray()) . PHP_EOL;

        return $buffer;
    }
}
