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

use PhpCsFixer\Config\NullRuleCustomisationPolicy;
use PhpCsFixer\Utils;

/**
 * @author Andreas Möller <am@localheinz.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Cache implements CacheInterface
{
    private SignatureInterface $signature;

    /**
     * @var array<string, string>
     */
    private array $hashes = [];

    public function __construct(SignatureInterface $signature)
    {
        $this->signature = $signature;
    }

    public function getSignature(): SignatureInterface
    {
        return $this->signature;
    }

    public function has(string $file): bool
    {
        return \array_key_exists($file, $this->hashes);
    }

    public function get(string $file): ?string
    {
        return $this->hashes[$file] ?? null;
    }

    public function set(string $file, string $hash): void
    {
        $this->hashes[$file] = $hash;
    }

    public function clear(string $file): void
    {
        unset($this->hashes[$file]);
    }

    public function toJson(): string
    {
        try {
            return json_encode(
                [
                    'php' => $this->getSignature()->getPhpVersion(),
                    'version' => $this->getSignature()->getFixerVersion(),
                    'indent' => $this->getSignature()->getIndent(),
                    'lineEnding' => $this->getSignature()->getLineEnding(),
                    'rules' => $this->getSignature()->getRules(),
                    'ruleCustomisationPolicyVersion' => $this->getSignature()->getRuleCustomisationPolicyVersion(),
                    'hashes' => $this->hashes,
                ],
                \JSON_THROW_ON_ERROR,
            );
        } catch (\JsonException $e) {
            throw new \UnexpectedValueException(\sprintf(
                'Cannot encode cache signature to JSON, error: "%s". If you have non-UTF8 chars in your signature, like in license for `header_comment`, consider enabling `ext-mbstring` or install `symfony/polyfill-mbstring`.',
                $e->getMessage(),
            ));
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromJson(string $json): self
    {
        try {
            $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException(\sprintf(
                'Value needs to be a valid JSON string, got "%s", error: "%s".',
                $json,
                $e->getMessage(),
            ));
        }

        $requiredKeys = [
            'php',
            'version',
            'indent',
            'lineEnding',
            'rules',
            // 'ruleCustomisationPolicyVersion', // @TODO v4: require me
            'hashes',
        ];

        $missingKeys = array_diff_key(array_flip($requiredKeys), $data);

        if (\count($missingKeys) > 0) {
            throw new \InvalidArgumentException(\sprintf(
                'JSON data is missing keys %s',
                Utils::naturalLanguageJoin(array_keys($missingKeys)),
            ));
        }

        // Validate value types too, not just key presence: a syntactically-valid cache with a
        // wrong-typed field must be treated as corrupt (rebuild) rather than crashing the process
        // with an uncaught TypeError from the strictly-typed Signature constructor / hash mapping.
        foreach (['php', 'version', 'indent', 'lineEnding'] as $stringKey) {
            if (!\is_string($data[$stringKey])) {
                throw new \InvalidArgumentException(\sprintf('JSON data key "%s" is expected to be a string.', $stringKey));
            }
        }

        if (!\is_array($data['rules'])) {
            throw new \InvalidArgumentException('JSON data key "rules" is expected to be an array.');
        }

        if (!\is_array($data['hashes'])) {
            throw new \InvalidArgumentException('JSON data key "hashes" is expected to be an array.');
        }

        if (isset($data['ruleCustomisationPolicyVersion']) && !\is_string($data['ruleCustomisationPolicyVersion'])) {
            throw new \InvalidArgumentException('JSON data key "ruleCustomisationPolicyVersion" is expected to be a string.');
        }

        $signature = new Signature(
            $data['php'],
            $data['version'],
            $data['indent'],
            $data['lineEnding'],
            $data['rules'],
            $data['ruleCustomisationPolicyVersion'] ?? NullRuleCustomisationPolicy::VERSION_FOR_CACHE,
        );

        $cache = new self($signature);

        // before v3.11.1 the hashes were crc32 encoded and saved as integers
        // @TODO v4: remove the to string cast/array_map
        $cache->hashes = array_map(static fn ($v): string => \is_int($v) ? (string) $v : $v, $data['hashes']);

        return $cache;
    }

    /**
     * @internal
     */
    public function backfillHashes(self $oldCache): bool
    {
        if (!$this->getSignature()->equals($oldCache->getSignature())) {
            return false;
        }

        $this->hashes = array_merge($oldCache->hashes, $this->hashes);

        return true;
    }
}
