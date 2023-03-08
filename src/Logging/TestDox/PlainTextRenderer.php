<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use function sprintf;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PlainTextRenderer
{
    /**
     * @psalm-param array<string, TestResultCollection> $tests
     */
    public function render(array $tests): string
    {
        $buffer = '';

        foreach ($tests as $prettifiedClassName => $_tests) {
            $buffer .= $prettifiedClassName . "\n";

            foreach ($this->reduce($_tests) as $prettifiedMethodName => $outcome) {
                $buffer .= sprintf(
                    ' [%s] %s' . "\n",
                    $outcome,
                    $prettifiedMethodName
                );
            }

            $buffer .= "\n";
        }

        return $buffer;
    }

    /**
     * @psalm-return array<string, 'x'|' '>
     */
    private function reduce(TestResultCollection $tests): array
    {
        $result = [];

        foreach ($tests as $test) {
            $prettifiedMethodName = $test->test()->testDox()->prettifiedMethodName();

            if (!isset($result[$prettifiedMethodName])) {
                $result[$prettifiedMethodName] = $test->status()->isSuccess() ? 'x' : ' ';

                continue;
            }

            if ($test->status()->isSuccess()) {
                continue;
            }

            $result[$prettifiedMethodName] = ' ';
        }

        return $result;
    }
}
