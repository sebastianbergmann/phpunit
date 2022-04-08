<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\ExcludeStaticPropertyFromBackup;
use PHPUnit\Framework\TestCase;

#[BackupStaticProperties(true)]
#[ExcludeStaticPropertyFromBackup('className', 'propertyName')]
final class BackupStaticPropertiesTest extends TestCase
{
    #[BackupStaticProperties(false)]
    #[ExcludeStaticPropertyFromBackup('anotherClassName', 'propertyName')]
    public function testOne(): void
    {
    }
}
