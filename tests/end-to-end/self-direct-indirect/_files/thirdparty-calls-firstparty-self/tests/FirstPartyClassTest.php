<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\SelfDirectIndirect;

use PHPUnit\Framework\TestCase;

final class FirstPartyClassTest extends TestCase
{
    /**
     * Third-party code calls first-party code that triggers a deprecation.
     * With ignoreSelfDeprecations=true, this deprecation should be filtered out
     * because the issue is triggered IN first-party code (isSelf() = true).
     */
    public function testThirdPartyCallsFirstParty(): void
    {
        $this->assertTrue((new ThirdPartyClass)->callFirstParty());
    }
}
