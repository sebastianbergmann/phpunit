<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

/**
 * The result of parsing the value of the --order-by CLI option or of the
 * executionOrder XML configuration attribute.
 *
 * A value that is null was not configured by the parsed value and must be
 * taken from elsewhere.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExecutionOrder
{
    private ?int $executionOrder;
    private ?int $executionOrderDefects;
    private ?bool $resolveDependencies;

    /**
     * @var list<string>
     */
    private array $unknownTokens;

    /**
     * @param list<string> $unknownTokens
     */
    public function __construct(?int $executionOrder, ?int $executionOrderDefects, ?bool $resolveDependencies, array $unknownTokens)
    {
        $this->executionOrder        = $executionOrder;
        $this->executionOrderDefects = $executionOrderDefects;
        $this->resolveDependencies   = $resolveDependencies;
        $this->unknownTokens         = $unknownTokens;
    }

    public function executionOrder(): ?int
    {
        return $this->executionOrder;
    }

    public function executionOrderDefects(): ?int
    {
        return $this->executionOrderDefects;
    }

    public function resolveDependencies(): ?bool
    {
        return $this->resolveDependencies;
    }

    /**
     * @return list<string>
     */
    public function unknownTokens(): array
    {
        return $this->unknownTokens;
    }
}
