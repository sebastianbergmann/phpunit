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

use PHPUnit\Framework\MockObject\Builder\InvocationMocker as BuilderInvocationMocker;
use PHPUnit\Framework\MockObject\Matcher\Invocation;

/**
 * @method BuilderInvocationMocker method($constraint)
 */
interface MockObject extends Stub
{
    public function __phpunit_setOriginalObject($originalObject): void;

    public function __phpunit_verify(bool $unsetInvocationMocker = true): void;

    public function expects(Invocation $matcher): BuilderInvocationMocker;
}
