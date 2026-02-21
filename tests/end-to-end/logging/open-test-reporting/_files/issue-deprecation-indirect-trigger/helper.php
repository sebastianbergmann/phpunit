<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
function phpunit_otr_indirect_trigger_inner(): void
{
    \trigger_error('message', \E_USER_DEPRECATED);
}

function phpunit_otr_indirect_trigger_outer(): void
{
    phpunit_otr_indirect_trigger_inner();
}
