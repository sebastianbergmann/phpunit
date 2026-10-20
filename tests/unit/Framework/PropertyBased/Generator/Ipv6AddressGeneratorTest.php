<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use function hex2bin;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Ipv6AddressGenerator::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class Ipv6AddressGeneratorTest extends TestCase
{
    /**
     * @return array<non-empty-string, array{non-empty-string, list<int>}>
     */
    public static function canonicalTextRepresentationProvider(): array
    {
        return [
            'unspecified address'                       => ['::', [0, 0, 0, 0, 0, 0, 0, 0]],
            'loopback'                                  => ['::1', [0, 0, 0, 0, 0, 0, 0, 1]],
            'run of zeros at the end'                   => ['1::', [1, 0, 0, 0, 0, 0, 0, 0]],
            'run of zeros in the middle'                => ['2001:db8::1', [0x2001, 0xDB8, 0, 0, 0, 0, 0, 1]],
            'no zeros'                                  => ['1:2:3:4:5:6:7:8', [1, 2, 3, 4, 5, 6, 7, 8]],
            'single group of zeros'                     => ['1:0:3:4:5:6:7:8', [1, 0, 3, 4, 5, 6, 7, 8]],
            'longest of two runs of zeros'              => ['1:0:0:4::8', [1, 0, 0, 4, 0, 0, 0, 8]],
            'first of two equally long runs of zeros'   => ['1::4:0:0:7:8', [1, 0, 0, 4, 0, 0, 7, 8]],
            'lowercase without leading zeros'           => ['abcd:ef:1:a0:0:b:c:d', [0xABCD, 0xEF, 1, 0xA0, 0, 0xB, 0xC, 0xD]],
            'IPv4-mapped address'                       => ['::ffff:192.0.2.1', [0, 0, 0, 0, 0, 0xFFFF, 0xC000, 0x201]],
            'IPv4-mapped unspecified address'           => ['::ffff:0.0.0.0', [0, 0, 0, 0, 0, 0xFFFF, 0, 0]],
            'deprecated IPv4-compatible address in hex' => ['::c000:201', [0, 0, 0, 0, 0, 0, 0xC000, 0x201]],
        ];
    }

    /**
     * @param non-empty-string $expected
     * @param list<int>        $groups
     */
    #[DataProvider('canonicalTextRepresentationProvider')]
    public function testFormatsAnAddressInItsCanonicalTextRepresentation(string $expected, array $groups): void
    {
        $this->assertSame($expected, Ipv6AddressGenerator::format($groups));
    }

    public function testSplitsTheBytesOfAnAddressIntoItsGroups(): void
    {
        $bytes = hex2bin('20010db8000000000000ff000042ffff');

        $this->assertNotFalse($bytes);
        $this->assertSame([0x2001, 0xDB8, 0, 0, 0, 0xFF00, 0x42, 0xFFFF], Ipv6AddressGenerator::groupsOf($bytes));
    }

    public function testClearsTheBitsOfAnAddressAfterItsPrefix(): void
    {
        $groups = [0x2001, 0xDB8, 0xFFFF, 0xFFFF, 0xFFFF, 0xFFFF, 0xFFFF, 0xFFFF];

        $this->assertSame([0x2001, 0xDB8, 0, 0, 0, 0, 0, 0], Ipv6AddressGenerator::withoutHostBits($groups, 32));
        $this->assertSame([0x2001, 0xDB8, 0xFFFF, 0xFFFF, 0xFFFF, 0xFFFF, 0xFFFF, 0xFFF0], Ipv6AddressGenerator::withoutHostBits($groups, 124));
        $this->assertSame([0x2000, 0, 0, 0, 0, 0, 0, 0], Ipv6AddressGenerator::withoutHostBits($groups, 7));
        $this->assertSame([0, 0, 0, 0, 0, 0, 0, 0], Ipv6AddressGenerator::withoutHostBits($groups, 0));
        $this->assertSame($groups, Ipv6AddressGenerator::withoutHostBits($groups, 128));
    }
}
