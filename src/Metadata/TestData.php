<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

use function is_string;
use PHPUnit\Event\InvalidArgumentException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 */
final class TestData extends Metadata
{
    private readonly array $data;
    private readonly ?string $name;

    /**
     * @throws InvalidArgumentException
     */
    protected function __construct(int $level, mixed ...$data)
    {
        parent::__construct($level);

        if (isset($data['name'])) {
            if (!is_string($data['name'])) {
                throw new InvalidArgumentException('Name must be of type string.');
            }

            $this->name = $data['name'];
            unset($data['name']);
        } else {
            $this->name = null;
        }

        $this->data = $data;
    }

    public function isTestWith(): bool
    {
        return true;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function name(): ?string
    {
        return $this->name;
    }
}
