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

/**
 * Constraint that checks if value is a resource of given type
 */
class IsResourceOfType extends Constraint
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var IsType
     */
    private $internalTypeConstraint;

    /**
     * @param string $type
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct(string $type)
    {
        parent::__construct();

        $this->internalTypeConstraint = new IsType('resource');
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    protected function matches($other): bool
    {
        return $this->internalTypeConstraint->evaluate($other, '', true)
            && $this->type === get_resource_type($other);
    }

    /**
     * @inheritdoc
     */
    public function toString(): string
    {
        return \sprintf(
            'is resource of type "%s"',
            $this->type
        );
    }
}
