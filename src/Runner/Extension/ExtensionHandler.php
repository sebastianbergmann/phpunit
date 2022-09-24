<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Extension;

use function class_exists;
use PHPUnit\Runner\ClassDoesNotExistException;
use PHPUnit\Runner\Exception;
use PHPUnit\TextUI\XmlConfiguration\Extension;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExtensionHandler
{
    /**
     * @throws Exception
     */
    public function registerExtension(Extension $extensionConfiguration): void
    {
        if (!class_exists($extensionConfiguration->className())) {
            throw new ClassDoesNotExistException($extensionConfiguration->className());
        }
    }
}
