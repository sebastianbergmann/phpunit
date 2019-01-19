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

use PHPUnit\Framework\SelfDescribing;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
interface Stub extends SelfDescribing
{
    /**
     * Fakes the processing of the invocation $invocation by returning a
     * specific value.
     *
     * @param Invocation $invocation The invocation which was mocked and matched by the current method and argument matchers
     */
    public function invoke(Invocation $invocation);
}
