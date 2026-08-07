<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestIndexSelection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Subject.php';

require_once __DIR__ . '/Helper.php';

#[CoversClass(Subject::class)]
#[UsesClass(Helper::class)]
#[RequiresPhpExtension('json')]
#[Group('a-group')]
final class SelectedTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
