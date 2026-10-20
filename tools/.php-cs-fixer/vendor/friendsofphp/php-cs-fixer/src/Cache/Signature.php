<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Cache;

use PhpCsFixer\Future;

/**
 * @author Andreas Möller <am@localheinz.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Signature implements SignatureInterface
{
    private string $phpVersion;

    private string $fixerVersion;

    private string $indent;

    private string $lineEnding;

    /**
     * @var array<string, array<string, mixed>|bool>
     */
    private array $rules;

    private string $ruleCustomisationPolicyVersion;

    /**
     * @param array<string, array<string, mixed>|bool> $rules
     */
    public function __construct(string $phpVersion, string $fixerVersion, string $indent, string $lineEnding, array $rules, string $ruleCustomisationPolicyVersion)
    {
        $this->phpVersion = $phpVersion;
        $this->fixerVersion = $fixerVersion;
        $this->indent = $indent;
        $this->lineEnding = $lineEnding;
        $this->rules = self::makeJsonEncodable($rules);
        $this->ruleCustomisationPolicyVersion = $ruleCustomisationPolicyVersion;
    }

    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    public function getFixerVersion(): string
    {
        return $this->fixerVersion;
    }

    public function getIndent(): string
    {
        return $this->indent;
    }

    public function getLineEnding(): string
    {
        return $this->lineEnding;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getRuleCustomisationPolicyVersion(): string
    {
        return $this->ruleCustomisationPolicyVersion;
    }

    public function equals(SignatureInterface $signature): bool
    {
        return $this->phpVersion === $signature->getPhpVersion()
            && $this->fixerVersion === $signature->getFixerVersion()
            && $this->indent === $signature->getIndent()
            && $this->lineEnding === $signature->getLineEnding()
            && $this->rules === $signature->getRules()
            && $this->ruleCustomisationPolicyVersion === $signature->getRuleCustomisationPolicyVersion();
    }

    /**
     * @param array<string, array<string, mixed>|bool> $data
     *
     * @return array<string, array<string, mixed>|bool>
     */
    private static function makeJsonEncodable(array $data): array
    {
        array_walk_recursive($data, static function (&$item, $key): void {
            if (\is_string($item) && false === mb_detect_encoding($item, 'utf-8', true)) {
                $item = base64_encode($item);
            } elseif (\is_object($item)) {
                if ($item instanceof \JsonSerializable) {
                    $item = \get_class($item).'#'.json_encode($item, \JSON_THROW_ON_ERROR);
                } else {
                    Future::triggerDeprecation(new \InvalidArgumentException(\sprintf(
                        'Can not serialize cache signature, unhandled object under "%s" key: "%s" - implement "%s".',
                        $key,
                        \get_class($item),
                        \JsonSerializable::class,
                    )));
                }
            }
        });

        return $data;
    }
}
