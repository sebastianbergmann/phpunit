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
     * @psalm-param array<class-string, TestMethodCollection> $tests
     */
    public function render(array $tests): string
    {
        $buffer     = '';
        $prettifier = new NamePrettifier;

        foreach ($tests as $className => $_tests) {
            $buffer .= $prettifier->prettifyTestClass($className) . "\n";

            foreach ($_tests as $test) {
                $buffer .= sprintf(
                    ' [%s] %s' . "\n",
                    $test->status()->isSuccess() ? 'x' : ' ',
                    $prettifier->prettifyTestMethod($test->test()->methodName())
                );
            }

            $buffer .= "\n";
        }

        return $buffer;
    }
}
