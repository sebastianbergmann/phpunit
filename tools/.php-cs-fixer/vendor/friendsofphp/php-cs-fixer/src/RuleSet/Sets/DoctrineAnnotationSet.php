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

namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDefinition;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DoctrineAnnotationSet extends AbstractRuleSetDefinition
{
    public function getRules(): array
    {
        return [
            'doctrine_annotation_array_assignment' => [
                'operator' => ':',
            ],
            'doctrine_annotation_braces' => true,
            'doctrine_annotation_indentation' => true,
            'doctrine_annotation_spaces' => [
                'before_array_assignments_colon' => false,
            ],
        ];
    }

    public function getDescription(): string
    {
        return 'Rules covering ``Doctrine`` annotations with configuration based on examples found in `Doctrine Annotation documentation <https://www.doctrine-project.org/projects/doctrine-annotations/en/latest/annotations.html>`_ and `Symfony documentation <https://symfony.com/doc/master/bundles/SensioFrameworkExtraBundle/annotations/routing.html>`_.';
    }
}
