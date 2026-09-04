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
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Runner\TestSuiteSorter;

/**
 * Parses the comma-separated list of tokens that the --order-by CLI option and
 * the executionOrder XML configuration attribute accept.
 *
 * This is the single place where those tokens are given meaning, so that both
 * configuration surfaces agree on how a value is interpreted and on which
 * values are diagnosed.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExecutionOrderParser
{
    /**
     * Tokens that select the order in which tests are sorted. At most one of
     * them is meaningful.
     *
     * @var non-empty-list<non-empty-string>
     */
    private const array MAIN_ORDER_TOKENS = [
        'default',
        'duration',
        'duration-ascending',
        'duration-descending',
        'random',
        'reverse',
        'size',
        'size-ascending',
        'size-descending',
    ];

    /**
     * @param string $value the configured value; every token it does not give
     *                      meaning to, including the empty token, is reported
     *                      back to the caller
     */
    public function parse(string $value, ExecutionOrderSource $source, ?int $executionOrder, ?int $executionOrderDefects, ?bool $resolveDependencies): ExecutionOrder
    {
        $unknownTokens = [];

        $previousMainOrderToken = null;
        $defectsSeen            = false;

        foreach (explode(',', $value) as $token) {
            if (in_array($token, self::MAIN_ORDER_TOKENS, true)) {
                if ($previousMainOrderToken !== null) {
                    $this->deprecateMultipleOrders($source, $previousMainOrderToken, $token);
                }

                if ($defectsSeen && $token !== 'default') {
                    $this->deprecateDefectsBeforeOrder($source, $token);

                    $defectsSeen = false;
                }

                $previousMainOrderToken = $token;
            }

            switch ($token) {
                case 'default':
                    $executionOrder        = TestSuiteSorter::ORDER_DEFAULT;
                    $executionOrderDefects = TestSuiteSorter::ORDER_DEFAULT;
                    $resolveDependencies   = true;
                    $defectsSeen           = false;

                    break;

                case 'defects':
                    $executionOrderDefects = TestSuiteSorter::ORDER_DEFECTS_FIRST;
                    $defectsSeen           = true;

                    break;

                case 'depends':
                    $resolveDependencies = true;

                    $this->deprecateDependencyToken($source, 'depends', $source->resolveDependencies());

                    break;

                case 'no-depends':
                    $resolveDependencies = false;

                    $this->deprecateDependencyToken($source, 'no-depends', $source->ignoreDependencies());

                    break;

                case 'duration':
                    $executionOrder = TestSuiteSorter::ORDER_DURATION_ASCENDING;

                    $this->deprecateRenamedToken($source, 'duration', 'duration-ascending');

                    break;

                case 'duration-ascending':
                    $executionOrder = TestSuiteSorter::ORDER_DURATION_ASCENDING;

                    break;

                case 'duration-descending':
                    $executionOrder = TestSuiteSorter::ORDER_DURATION_DESCENDING;

                    break;

                case 'random':
                    $executionOrder = TestSuiteSorter::ORDER_RANDOMIZED;

                    break;

                case 'reverse':
                    $executionOrder = TestSuiteSorter::ORDER_REVERSED;

                    break;

                case 'size':
                    $executionOrder = TestSuiteSorter::ORDER_SIZE_ASCENDING;

                    $this->deprecateRenamedToken($source, 'size', 'size-ascending');

                    break;

                case 'size-ascending':
                    $executionOrder = TestSuiteSorter::ORDER_SIZE_ASCENDING;

                    break;

                case 'size-descending':
                    $executionOrder = TestSuiteSorter::ORDER_SIZE_DESCENDING;

                    break;

                default:
                    $unknownTokens[] = $token;
            }
        }

        return new ExecutionOrder(
            $executionOrder,
            $executionOrderDefects,
            $resolveDependencies,
            $unknownTokens,
        );
    }

    /**
     * @param non-empty-string $token
     * @param non-empty-string $replacement
     */
    private function deprecateRenamedToken(ExecutionOrderSource $source, string $token, string $replacement): void
    {
        EventFacade::emitter()->testRunnerTriggeredPhpunitDeprecation(
            sprintf(
                'Using "%s" for %s is deprecated and will be removed in PHPUnit 14. Use "%s" instead.',
                $token,
                $source->subject(),
                $replacement,
            ),
        );
    }

    /**
     * Whether dependencies between tests are resolved is not an ordering
     * strategy, and it has had a dedicated configuration option all along.
     *
     * @param non-empty-string $token
     * @param non-empty-string $replacement
     */
    private function deprecateDependencyToken(ExecutionOrderSource $source, string $token, string $replacement): void
    {
        EventFacade::emitter()->testRunnerTriggeredPhpunitDeprecation(
            sprintf(
                'Using "%s" for %s is deprecated and will be removed in PHPUnit 14. Use %s instead.',
                $token,
                $source->subject(),
                $replacement,
            ),
        );
    }

    /**
     * @param non-empty-string $previousToken
     * @param non-empty-string $token
     */
    private function deprecateMultipleOrders(ExecutionOrderSource $source, string $previousToken, string $token): void
    {
        EventFacade::emitter()->testRunnerTriggeredPhpunitDeprecation(
            sprintf(
                'Using more than one order for %s is deprecated and will be an error in PHPUnit 14. "%s" overrides "%s".',
                $source->subject(),
                $token,
                $previousToken,
            ),
        );
    }

    /**
     * @param non-empty-string $token
     */
    private function deprecateDefectsBeforeOrder(ExecutionOrderSource $source, string $token): void
    {
        EventFacade::emitter()->testRunnerTriggeredPhpunitDeprecation(
            sprintf(
                'Using "defects" before "%s" for %s is deprecated and will change meaning in PHPUnit 14, where tests are reordered in the order in which the tokens are written. Use "%s,defects" instead.',
                $token,
                $source->subject(),
                $this->canonicalOrderToken($token),
            ),
        );
    }

    /**
     * The replacement suggested by a diagnostic must never be a token that is
     * itself deprecated.
     *
     * @param non-empty-string $token
     *
     * @return non-empty-string
     */
    private function canonicalOrderToken(string $token): string
    {
        if ($token === 'duration') {
            return 'duration-ascending';
        }

        if ($token === 'size') {
            return 'size-ascending';
        }

        return $token;
    }
}
