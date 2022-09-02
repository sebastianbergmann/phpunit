<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Phpt extends Test
{
    public function __construct(string $file)
    {
        parent::__construct($file);
    }

    /**
     * @psalm-assert-if-true Phpt $this
     */
    public function isPhpt(): bool
    {
        return true;
    }

    public function id(): string
    {
        return $this->file();
    }

    public function name(): string
    {
        return $this->file();
    }
}
