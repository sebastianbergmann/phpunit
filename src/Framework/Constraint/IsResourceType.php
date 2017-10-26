<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Util\InvalidArgumentHelper;

/**
 * Constraint that checks if value is resource type
 *
 * The file path to check is passed as $other in evaluate().
 */
class IsResourceType extends Constraint
{
    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct($type)
    {
        parent::__construct();

        if (!\is_string($type)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    protected function matches($other)
    {
        return $this->type === get_resource_type($other);
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return \sprintf(
            'is resource of type "%s"',
            $this->type
        );
    }
}
