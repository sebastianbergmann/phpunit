<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

/**
 * @psalm-immutable
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Result
{
    private string $output;
    private bool $success;

    public static function from(string $output = '', bool $success = true): self
    {
        return new self($output, $success);
    }

    private function __construct(string $output, bool $success)
    {
        $this->output  = $output;
        $this->success = $success;
    }

    public function output(): string
    {
        return $this->output;
    }

    public function wasSuccessful(): bool
    {
        return $this->success;
    }
}
