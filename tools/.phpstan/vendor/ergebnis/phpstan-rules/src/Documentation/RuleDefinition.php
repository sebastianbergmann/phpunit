<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules\Documentation;

/**
 * @internal
 */
final class RuleDefinition
{
    private ConfigurationKey $configurationKey;
    private Description $description;
    private Rationale $rationale;
    private Tip $tip;
    private ParameterList $parameters;
    private CodeSampleList $codeSamples;

    private function __construct(
        ConfigurationKey $configurationKey,
        Description $description,
        Rationale $rationale,
        Tip $tip,
        ParameterList $parameters,
        CodeSampleList $codeSamples
    ) {
        $this->configurationKey = $configurationKey;
        $this->description = $description;
        $this->rationale = $rationale;
        $this->tip = $tip;
        $this->parameters = $parameters;
        $this->codeSamples = $codeSamples;
    }

    public static function create(
        ConfigurationKey $configurationKey,
        Description $description,
        Rationale $rationale,
        Tip $tip,
        ParameterList $parameters,
        CodeSampleList $codeSamples
    ): self {
        return new self(
            $configurationKey,
            $description,
            $rationale,
            $tip,
            $parameters,
            $codeSamples,
        );
    }

    public function configurationKey(): ConfigurationKey
    {
        return $this->configurationKey;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public function rationale(): Rationale
    {
        return $this->rationale;
    }

    public function tip(): Tip
    {
        return $this->tip;
    }

    public function parameters(): ParameterList
    {
        return $this->parameters;
    }

    public function codeSamples(): CodeSampleList
    {
        return $this->codeSamples;
    }
}
