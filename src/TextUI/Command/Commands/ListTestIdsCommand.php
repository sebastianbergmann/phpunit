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
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ListTestIdsCommand implements Command
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
        $buffer = '';

        foreach ($this->tests as $test) {
            $buffer .= $test->valueObjectForEvents()->id() . PHP_EOL;
        }

        return Result::from($buffer);
    }
}
