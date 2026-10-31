<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\StateMachine;

use function strtoupper;
use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\Invariant;
use PHPUnit\Framework\Attributes\Rule;
use RuntimeException;
use stdClass;

final class SwitchMachine
{
    /**
     * @var list<non-empty-string>
     */
    public array $calls               = [];
    public bool $invariantFailsWhenOn = false;
    public bool $useTokenFails        = false;
    private bool $on                  = false;
    private int $numberOfTokens       = 0;

    #[Rule(precondition: 'isOff')]
    public function switchOn(): void
    {
        $this->calls[] = 'switchOn()';

        $this->on = true;
    }

    #[Rule(precondition: 'isOn')]
    public function switchOff(): void
    {
        $this->calls[] = 'switchOff()';

        $this->on = false;
    }

    #[Rule(produces: 'tokens')]
    public function token(): string
    {
        $this->numberOfTokens++;

        $this->calls[] = 'token()';

        return 'token' . $this->numberOfTokens;
    }

    #[Rule]
    public function useToken(#[FromPool('tokens')] string $token): string
    {
        $this->calls[] = 'useToken(' . $token . ')';

        if ($this->useTokenFails) {
            throw new RuntimeException('The token cannot be used');
        }

        return strtoupper($token);
    }

    #[Rule]
    public function describe(): stdClass
    {
        $this->calls[] = 'describe()';

        $description     = new stdClass;
        $description->on = $this->on;

        return $description;
    }

    public function isOn(): bool
    {
        return $this->on;
    }

    public function isOff(): bool
    {
        return !$this->on;
    }

    #[Invariant]
    public function invariant(): void
    {
        $this->calls[] = 'invariant()';

        if ($this->invariantFailsWhenOn && $this->on) {
            throw new RuntimeException('The switch is on');
        }
    }
}
