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

use function preg_match;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class IgnorePhpunitWarnings extends Metadata
{
    /** @var null|non-empty-string */
    private ?string $messagePattern;

    /**
     * @param int<0, 1>             $level
     * @param null|non-empty-string $messagePattern
     */
    protected function __construct(int $level, null|string $messagePattern)
    {
        parent::__construct($level);

        $this->messagePattern = $messagePattern;
    }

    public function isIgnorePhpunitWarnings(): true
    {
        return true;
    }

    public function shouldIgnore(string $message): bool
    {
        return $this->messagePattern === null ||
            (bool) preg_match('{' . $this->messagePattern . '}', $message);
    }
}
