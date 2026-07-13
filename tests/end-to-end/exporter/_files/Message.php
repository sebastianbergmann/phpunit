<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ObjectExporter;

use function range;

final class Message
{
    private string $text;

    /**
     * @var list<int>
     */
    private array $payload;

    public function __construct(string $text)
    {
        $this->text    = $text;
        $this->payload = range(1, 3);
    }

    public function text(): string
    {
        return $this->text;
    }

    /**
     * @return list<int>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
