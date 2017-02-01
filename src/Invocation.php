<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\MockObject;

/**
 * Interface for invocations.
 *
 * @since Interface available since Release 1.0.0
 */
interface Invocation
{
    /**
     * @return mixed Mocked return value.
     */
    public function generateReturnValue();
}
