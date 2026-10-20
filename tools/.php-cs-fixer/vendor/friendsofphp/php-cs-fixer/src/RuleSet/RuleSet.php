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

namespace PhpCsFixer\RuleSet;

use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Future;
use PhpCsFixer\Utils;

/**
 * Set of rules to be used by fixer.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class RuleSet implements RuleSetInterface
{
    /**
     * Group of rules generated from input set.
     *
     * The key is name of rule, value is configuration array or true.
     * The key must not point to any set.
     *
     * @var array<string, array<string, mixed>|true>
     */
    private array $rules;

    public function __construct(array $set = [])
    {
        foreach ($set as $name => $value) {
            if ('' === $name) {
                throw new \InvalidArgumentException('Rule/set name must not be empty.');
            }

            if (\is_int($name)) {
                throw new \InvalidArgumentException(\sprintf('Missing value for "%s" rule/set.', $value));
            }

            if (!\is_bool($value) && !\is_array($value)) {
                $message = str_starts_with($name, '@') ? 'Set must be enabled (true) or disabled (false). Other values are not allowed.' : 'Rule must be enabled (true), disabled (false) or configured (non-empty, assoc array). Other values are not allowed.';

                if (null === $value) {
                    $message .= ' To disable the '.(str_starts_with($name, '@') ? 'set' : 'rule').', use "FALSE" instead of "NULL".';
                }

                throw new InvalidFixerConfigurationException($name, $message);
            }
        }

        $this->rules = $this->resolveSet($set);
    }

    public function hasRule(string $rule): bool
    {
        return \array_key_exists($rule, $this->rules);
    }

    public function getRuleConfiguration(string $rule): ?array
    {
        if (!$this->hasRule($rule)) {
            throw new \InvalidArgumentException(\sprintf('Rule "%s" is not in the set.', $rule));
        }

        if (true === $this->rules[$rule]) {
            return null;
        }

        return $this->rules[$rule];
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Resolve input set into group of rules.
     *
     * @param array<string, array<string, mixed>|bool> $rules
     *
     * @return array<string, array<string, mixed>|true>
     */
    private function resolveSet(array $rules): array
    {
        $resolvedRules = [];

        // expand sets
        foreach ($rules as $name => $value) {
            if (str_starts_with($name, '@')) {
                if (!\is_bool($value)) {
                    throw new \UnexpectedValueException(\sprintf('Nested rule set "%s" configuration must be a boolean.', $name));
                }

                $set = $this->resolveSubset($name, $value);
                $resolvedRules = array_merge($resolvedRules, $set);
            } else {
                $resolvedRules[$name] = $value;
            }
        }

        // filter out all resolvedRules that are off
        $resolvedRules = array_filter(
            $resolvedRules,
            static fn ($value): bool => false !== $value,
        );

        return $resolvedRules;
    }

    /**
     * Resolve set rules as part of another set.
     *
     * If set value is false then disable all fixers in set,
     * if not then get value from set item.
     *
     * @return array<string, array<string, mixed>|bool>
     */
    private function resolveSubset(string $setName, bool $setValue): array
    {
        $ruleSet = RuleSets::getSetDefinition($setName);

        if ($ruleSet instanceof DeprecatedRuleSetDefinitionInterface) {
            $messageEnd = [] === $ruleSet->getSuccessorsNames()
                ? 'No replacement available'
                : \sprintf('Use %s instead', Utils::naturalLanguageJoin($ruleSet->getSuccessorsNames()));

            Future::triggerDeprecation(new \RuntimeException("Rule set \"{$setName}\" is deprecated. {$messageEnd}."));
        }

        $rules = $ruleSet->getRules();

        foreach ($rules as $name => $value) {
            if (str_starts_with($name, '@')) {
                $set = $this->resolveSubset($name, $setValue);
                unset($rules[$name]);
                $rules = array_merge($rules, $set);
            } elseif (!$setValue) {
                $rules[$name] = false;
            } else {
                $rules[$name] = $value;
            }
        }

        return $rules;
    }
}
