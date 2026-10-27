<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use PHPUnit\TextUI\Configuration\GroupCollection;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Groups
{
    private GroupCollection $include;
    private GroupCollection $exclude;

    public function __construct(GroupCollection $include, GroupCollection $exclude)
    {
        $this->include = $include;
        $this->exclude = $exclude;
    }

    public function include(): GroupCollection
    {
        return $this->include;
    }

    public function exclude(): GroupCollection
    {
        return $this->exclude;
    }
}
