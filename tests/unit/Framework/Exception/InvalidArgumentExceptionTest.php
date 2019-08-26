<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

/**
 * @small
 */
final class InvalidArgumentExceptionTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testUsesCorrectArticleInErrorMessage(string $expected, $type): void
    {
        $e = InvalidArgumentException::create(1, $type);

        $this->assertStringMatchesFormat($expected, $e->getMessage());
    }

    public function provider(): array
    {
        return [
            'an array'  => ['Argument #1 of %s must be an array', 'array'],
            'a boolean' => ['Argument #1 of %s must be a boolean', 'boolean'],
        ];
    }
}
