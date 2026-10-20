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

namespace PhpCsFixer\DocBlock;

/**
 * This class is responsible for comparing tags to see if they should be kept
 * together, or kept apart.
 *
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 * @author Jakub Kwaśniewski <jakub@zero-85.pl>
 *
 * @deprecated
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TagComparator
{
    /**
     * Groups of tags that should be allowed to immediately follow each other.
     *
     * @var non-empty-list<non-empty-list<string>>
     *
     * @internal
     */
    public const DEFAULT_GROUPS = [
        ['deprecated', 'link', 'see', 'since'],
        ['author', 'copyright', 'license'],
        ['category', 'package', 'subpackage'],
        ['property', 'property-read', 'property-write'],
    ];

    /**
     * Should the given tags be kept together, or kept apart?
     *
     * @param list<list<string>> $groups
     */
    public static function shouldBeTogether(Tag $first, Tag $second, array $groups = self::DEFAULT_GROUPS): bool
    {
        @trigger_error('Method '.__METHOD__.' is deprecated and will be removed in version 4.0.', \E_USER_DEPRECATED);

        $firstName = $first->getName();
        $secondName = $second->getName();

        if ($firstName === $secondName) {
            return true;
        }

        foreach ($groups as $group) {
            if (\in_array($firstName, $group, true) && \in_array($secondName, $group, true)) {
                return true;
            }
        }

        return false;
    }
}
