<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use function sort;
use function sprintf;
use function str_starts_with;
use PHPUnit\Framework\TestSuite;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ListGroupsCommand implements Command
{
    private TestSuite $suite;

    public function __construct(TestSuite $suite)
    {
        $this->suite = $suite;
    }

    public function execute(): Result
    {
        $buffer = 'Available test group(s):' . PHP_EOL;
        $groups = $this->suite->getGroups();

        sort($groups);

        foreach ($groups as $group) {
            if (str_starts_with($group, '__phpunit_')) {
                continue;
            }

            $buffer .= sprintf(
                ' - %s' . PHP_EOL,
                $group
            );
        }

        return Result::from($buffer);
    }
}
