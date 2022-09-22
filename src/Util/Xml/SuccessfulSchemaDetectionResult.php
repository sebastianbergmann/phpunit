<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Xml;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 */
final class SuccessfulSchemaDetectionResult extends SchemaDetectionResult
{
    private readonly string $version;

    public function __construct(string $version)
    {
        $this->version = $version;
    }

    public function detected(): bool
    {
        return true;
    }

    public function version(): string
    {
        return $this->version;
    }
}
