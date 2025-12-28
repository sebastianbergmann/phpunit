<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Builder;

/**
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface Identity
{
    /**
     * Sets the identification of the expectation to $id.
     *
     * @note The identifier is unique per mock object.
     *
     * @param string $id unique identification of expectation
     */
    public function id($id);
}
