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

namespace PhpCsFixer\Documentation;

use PhpCsFixer\Console\Application;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\Fixer\ExperimentalFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\InternalFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionInterface;
use PhpCsFixer\RuleSet\AutomaticRuleSetDefinitionInterface;
use PhpCsFixer\RuleSet\DeprecatedRuleSetDefinitionInterface;
use PhpCsFixer\RuleSet\InternalRuleSetDefinitionInterface;
use PhpCsFixer\RuleSet\RuleSetDefinitionInterface;
use PhpCsFixer\Utils;

/**
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DocumentationTagGenerator
{
    /**
     * @return list<DocumentationTag>
     */
    public static function analyseRuleSet(RuleSetDefinitionInterface $ruleSetDefinition): array
    {
        $tags = [];

        // not possible for set to be DocumentationTagType::EXPERIMENTAL

        if ($ruleSetDefinition instanceof InternalRuleSetDefinitionInterface) {
            $tags[] = new DocumentationTag(
                DocumentationTagType::INTERNAL,
                'This rule set is INTERNAL',
                'Set is expected to be used only on PHP CS Fixer project itself.',
            );
        }

        if ($ruleSetDefinition instanceof DeprecatedRuleSetDefinitionInterface) {
            $alternatives = $ruleSetDefinition->getSuccessorsNames();

            $tags[] = new DocumentationTag(
                DocumentationTagType::DEPRECATED,
                \sprintf('This rule set is DEPRECATED and will be removed in the next major version %d.0', Application::getMajorVersion() + 1),
                0 !== \count($alternatives)
                    ? \sprintf(
                        'You should use %s instead.',
                        Utils::naturalLanguageJoinWithBackticks($alternatives),
                    )
                    : 'No replacement available.',
            );
        }

        if ($ruleSetDefinition->isRisky()) {
            $tags[] = new DocumentationTag(
                DocumentationTagType::RISKY,
                'This rule set is RISKY',
                'This set contains rules that are risky. Using it may lead to changes in your code\'s logic and behaviour. Use it with caution and review changes before incorporating them into your code base.',
            );
        }

        // not possible for set to be DocumentationTagType::CONFIGURABLE

        if ($ruleSetDefinition instanceof AutomaticRuleSetDefinitionInterface) {
            $tags[] = new DocumentationTag(
                DocumentationTagType::AUTOMATIC,
                'This rule set is AUTOMATIC',
                '⚡ '.strip_tags(AutomaticRuleSetDefinitionInterface::WARNING_MESSAGE_DECORATED),
            );
        }

        return $tags;
    }

    /**
     * @return list<DocumentationTag>
     */
    public static function analyseRule(FixerInterface $fixer): array
    {
        $tags = [];

        if ($fixer instanceof ExperimentalFixerInterface) {
            $tags[] = new DocumentationTag(
                DocumentationTagType::EXPERIMENTAL,
                'This rule is EXPERIMENTAL',
                'Rule is not covered with backward compatibility promise and may produce unstable or unexpected results, use it at your own risk. Rule\'s behaviour may be changed at any point, including rule\'s name; its options\' names, availability and allowed values; its default configuration. Rule may be even removed without prior notice. Feel free to provide feedback and help with determining final state of the rule.',
            );
        }

        if ($fixer instanceof InternalFixerInterface) {
            $tags[] = new DocumentationTag(
                DocumentationTagType::INTERNAL,
                'This rule is INTERNAL',
                'Rule is expected to be used only on PHP CS Fixer project itself.',
            );
        }

        if ($fixer instanceof DeprecatedFixerInterface) {
            $alternatives = $fixer->getSuccessorsNames();

            $tags[] = new DocumentationTag(
                DocumentationTagType::DEPRECATED,
                \sprintf('This rule is DEPRECATED and will be removed in the next major version %d.0', Application::getMajorVersion() + 1),
                0 !== \count($alternatives)
                    ? \sprintf(
                        'You should use %s instead.',
                        Utils::naturalLanguageJoinWithBackticks($alternatives),
                    )
                    : 'No replacement available.',
            );
        }

        if ($fixer->isRisky()) {
            $riskyDescription = $fixer->getDefinition()->getRiskyDescription();

            $tags[] = new DocumentationTag(
                DocumentationTagType::RISKY,
                'This rule is RISKY',
                // @TODO - FRS enable me
                // 'Using it may lead to changes in your code\'s logic and behaviour. Use it with caution and review changes before incorporating them into your code base.'
                // \n\n
                ''
                .(null !== $riskyDescription ? "{$riskyDescription}" : ''),
            );
        }

        if ($fixer instanceof ConfigurableFixerInterface) {
            $options = array_map(
                static fn (FixerOptionInterface $option): string => '`'.$option->getName().'`',
                $fixer->getConfigurationDefinition()->getOptions(),
            );
            $tags[] = new DocumentationTag(
                DocumentationTagType::CONFIGURABLE,
                'This rule is CONFIGURABLE',
                \sprintf(
                    'You can configure this rule using the following option%s: %s.',
                    1 === \count($options) ? '' : 's',
                    implode(', ', $options),
                ),
            );
        }

        // not possible for set to be DocumentationTagType::AUTOMATIC

        return $tags;
    }
}
