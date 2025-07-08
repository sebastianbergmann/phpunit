<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class IgnorePhpunitWarnings
{
    /** @var null|non-empty-string */
    private ?string $messagePattern;

    /**
     * @param null|non-empty-string $messagePattern
     */
    public function __construct(null|string $messagePattern = null)
    {
        $this->messagePattern = $messagePattern;
    }

    /**
     * @return null|non-empty-string
     */
    public function messagePattern(): ?string
    {
        return $this->messagePattern;
    }
}
