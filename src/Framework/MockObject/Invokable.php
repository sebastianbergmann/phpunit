<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
interface Invokable extends Verifiable
{
    /**
     * Invokes the invocation object $invocation so that it can be checked for
     * expectations or matched against stubs.
     *
     * @param Invocation $invocation The invocation object passed from mock object
     *
     * @return object
     */
    public function invoke(Invocation $invocation);

    /**
     * Checks if the invocation matches.
     *
     * @param Invocation $invocation The invocation object passed from mock object
     */
    public function matches(Invocation $invocation): bool;
}
