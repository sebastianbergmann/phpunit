<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\PropertyBased;

use function sprintf;
use PHPUnit\Exception;
use RuntimeException;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class AfterLastTestMethodFailedException extends RuntimeException implements Exception
{
    /**
     * @var class-string
     */
    private readonly string $className;

    /**
     * @param class-string $className the test class
     * @param Throwable    $previous  the throwable that the hook method raised
     */
    public function __construct(string $className, Throwable $previous)
    {
        $this->className = $className;

        $message = sprintf(
            'A hook method of %s that is called after its last test raised %s',
            $className,
            $previous::class,
        );

        if ($previous->getMessage() !== '') {
            $message .= ': ' . $previous->getMessage();
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * The test class whose hook method raised the throwable, which is the
     * previous exception of this one.
     *
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }
}
