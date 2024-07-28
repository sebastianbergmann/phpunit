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

use const PHP_EOL;
use function array_merge;
use function array_unique;
use function sort;
use function sprintf;
use function str_starts_with;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\PhptTestCase;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ListGroupsCommand implements Command
{
    /**
     * @var list<PhptTestCase|TestCase>
     */
    private array $tests;

    /**
     * @param list<PhptTestCase|TestCase> $tests
     */
    public function __construct(array $tests)
    {
        $this->tests = $tests;
    }

    public function execute(): Result
    {
        $groups = [];

        foreach ($this->tests as $test) {
            if ($test instanceof PhptTestCase) {
                $groups[] = 'default';

                continue;
            }

            $groups = array_merge($groups, $test->groups());
        }

        $groups = array_unique($groups);

        sort($groups);

        $buffer = 'Available test group(s):' . PHP_EOL;

        foreach ($groups as $group) {
            if (str_starts_with($group, '__phpunit_')) {
                continue;
            }

            $buffer .= sprintf(
                ' - %s' . PHP_EOL,
                $group,
            );
        }

        return Result::from($buffer);
    }
}
