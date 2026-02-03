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
     * Third-party code calls third-party code that triggers a deprecation.
     * This is a "pure indirect" scenario (caller=ThirdParty, callee=ThirdParty).
     * With ignoreSelfDeprecations=true and ignoreDirectDeprecations=true,
     * only indirect deprecations are reported.
     */
    public function testPureIndirect(): void
    {
        $this->assertTrue((new FirstPartyClass)->method());
    }
}
