<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function sprintf;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class MarkedIncompleteOrSkippedInAfterHookMethodException extends Exception
{
    /**
     * @param 'after-last-test'|'after-test' $typeOfHookMethod
     * @param class-string                   $className
     * @param non-empty-string               $methodName
     */
    public function __construct(IncompleteTest|SkippedTest $previous, string $typeOfHookMethod, string $className, string $methodName)
    {
        $markingMethod = 'markTestSkipped';

        if ($previous instanceof IncompleteTest) {
            $markingMethod = 'markTestIncomplete';
        }

        parent::__construct(
            sprintf(
                '%s() must not be called in %s method %s::%s()',
                $markingMethod,
                $typeOfHookMethod,
                $className,
                $methodName,
            ),
            0,
            $previous,
        );
    }
}
