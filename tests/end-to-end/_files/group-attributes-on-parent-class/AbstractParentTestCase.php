<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\GroupAttributesOnParentClass;

require_once __DIR__ . '/AbstractGrandparentTestCase.php';

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Ticket;

#[Group('one')]
#[Group('two')]
#[Ticket('1234')]
#[Small]
abstract class AbstractParentTestCase extends AbstractGrandparentTestCase
{
}
