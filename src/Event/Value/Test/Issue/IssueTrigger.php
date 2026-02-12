<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code\IssueTrigger;

use function sprintf;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class IssueTrigger
{
    private ?Code $callee;
    private ?Code $caller;

    public static function unknown(): self
    {
        return new self(null, null);
    }

    public static function test(): self
    {
        return new self(Code::Test, null);
    }

    public static function from(Code $callee, ?Code $caller = null): self
    {
        return new self($callee, $caller);
    }

    private function __construct(?Code $callee, ?Code $caller)
    {
        $this->callee = $callee;
        $this->caller = $caller;
    }

    /**
     * An issue is triggered in test code.
     */
    public function isTest(): bool
    {
        return $this->callee == Code::Test;
    }

    /**
     * An issue is triggered in first-party code or in test code.
     */
    public function isSelf(): bool
    {
        return $this->callee !== null && $this->callee->isFirstPartyOrTest();
    }

    /**
     * First-party code triggers an issue in third-party code.
     */
    public function isDirect(): bool
    {
        return $this->caller !== null && $this->caller->isFirstPartyOrTest() &&
               $this->callee !== null && $this->callee->isThirdPartyOrPhp();
    }

    /**
     * Third-party code triggers an issue.
     */
    public function isIndirect(): bool
    {
        return $this->caller !== null && $this->caller == Code::ThirdParty &&
               $this->callee !== null && $this->callee->isThirdPartyOrPhp();
    }

    public function isUnknown(): bool
    {
        return $this->callee === null;
    }

    public function asString(): string
    {
        if ($this->isUnknown()) {
            return 'unknown if issue was triggered in first-party code or third-party code';
        }

        if ($this->isTest()) {
            return 'issue triggered by test code';
        }

        return sprintf(
            'issue triggered by %s calling into %s',
            $this->caller->value,
            $this->callee->value,
        );
    }
}
