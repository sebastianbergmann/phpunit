<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use function explode;
use function in_array;
use function sprintf;
use PHPUnit\Runner\ExecutionOrder\Order;

/**
 * Parses the comma-separated list of tokens that the --order-by CLI option and
 * the executionOrder XML configuration attribute accept.
 *
 * This is the single place where those tokens are given meaning, so that both
 * configuration surfaces agree on how a value is interpreted and on which
 * values are rejected.
 *
 * Tokens are applied in the order in which they are written, so
 * "duration-ascending,defects" sorts the tests by duration and then hoists the
 * defective ones, while "defects,duration-ascending" does the opposite and lets
 * the duration sort discard the hoist.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExecutionOrderParser
{
    /**
     * Values that were supported by earlier versions of PHPUnit, mapped to what
     * to tell the user to write instead.
     *
     * @var non-empty-array<non-empty-string, non-empty-string>
     */
    private const array RENAMED = [
        'duration' => '"duration-ascending"',
        'size'     => '"size-ascending"',
    ];

    /**
     * @param string $value the configured value
     *
     * @throws InvalidExecutionOrderException
     *
     * @return list<Order>
     */
    public function parse(string $value, ExecutionOrderSource $source): array
    {
        $order = [];

        foreach (explode(',', $value) as $token) {
            if ($token === 'default') {
                $order = [];

                continue;
            }

            $element = Order::fromToken($token);

            if ($element === null) {
                throw new InvalidExecutionOrderException(
                    $this->messageForUnsupportedToken($token, $source),
                );
            }

            if (in_array($element, $order, true)) {
                throw new InvalidExecutionOrderException(
                    sprintf(
                        'Cannot use "%s" more than once for %s',
                        $element->token(),
                        $source->subject(),
                    ),
                );
            }

            if ($element->isSortingStrategy()) {
                foreach ($order as $configured) {
                    if ($configured->isSortingStrategy()) {
                        throw new InvalidExecutionOrderException(
                            sprintf(
                                'Cannot use more than one order for %s: "%s" and "%s"',
                                $source->subject(),
                                $configured->token(),
                                $element->token(),
                            ),
                        );
                    }
                }
            }

            $order[] = $element;
        }

        return $order;
    }

    /**
     * @return non-empty-string
     */
    private function messageForUnsupportedToken(string $token, ExecutionOrderSource $source): string
    {
        if ($token === 'depends') {
            return sprintf(
                '"depends" is no longer supported for %s, use %s instead',
                $source->subject(),
                $source->resolveDependencies(),
            );
        }

        if ($token === 'no-depends') {
            return sprintf(
                '"no-depends" is no longer supported for %s, use %s instead',
                $source->subject(),
                $source->ignoreDependencies(),
            );
        }

        if (isset(self::RENAMED[$token])) {
            return sprintf(
                '"%s" is no longer supported for %s, use %s instead',
                $token,
                $source->subject(),
                self::RENAMED[$token],
            );
        }

        return sprintf(
            'Unknown value "%s" for %s',
            $token,
            $source->subject(),
        );
    }
}
