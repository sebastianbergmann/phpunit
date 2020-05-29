<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

/**
 * @small
 */
final class LogicalAndTest extends ConnectiveTestCase
{
    public static function getOperatorName(): string
    {
        return 'and';
    }

    public static function getOperatorPrecedence(): int
    {
        return 22;
    }

    public function evaluateExpectedResult(array $input): bool
    {
        $initial = (bool) \array_shift($input);

        return \array_reduce($input, function ($carry, bool $item) {
            return $carry && $item;
        }, $initial);
    }
}
