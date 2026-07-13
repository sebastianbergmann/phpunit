<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ObjectExporter;

use function assert;
use function sprintf;
use SebastianBergmann\Exporter\Exporter;
use SebastianBergmann\Exporter\ObjectExporter;

final class MessageExporter implements ObjectExporter
{
    public function handles(object $object): bool
    {
        return $object instanceof Message;
    }

    public function export(object $object, Exporter $exporter, int $indentation): string
    {
        assert($object instanceof Message);

        return sprintf('Message ("%s")', $object->text());
    }
}
