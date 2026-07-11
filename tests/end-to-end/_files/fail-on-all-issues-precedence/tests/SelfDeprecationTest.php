<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\FailOnAllIssuesPrecedence;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/FirstParty.php';

final class SelfDeprecationTest extends TestCase
{
    public function testSelfDeprecation(): void
    {
        (new FirstParty)->triggerDeprecation();

        $this->assertTrue(true);
    }
}
