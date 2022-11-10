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

            foreach ($_tests as $test) {
                $buffer .= sprintf(
                    ' [%s] %s' . "\n",
                    $test->status()->isSuccess() ? 'x' : ' ',
                    $test->test()->testDox()->prettifiedMethodName()
                );
            }

            $buffer .= "\n";
        }

        return $buffer;
    }
}
