<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelDataProvider;

use function getenv;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * An object that, like the user entity of a web application's security
 * component, keeps only its identifier when serialized: an email address
 * that travelled by serialization would not arrive.
 */
final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
    ) {
    }

    /**
     * @return array{id: int}
     */
    public function __serialize(): array
    {
        return ['id' => $this->id];
    }

    /**
     * @param array{id: int} $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
    }
}

final class LossySerializationDataTest extends TestCase
{
    public static function userProvider(): array
    {
        return [
            'admin' => [new User(1, 'admin@example.com')],
        ];
    }

    #[DataProvider('userProvider')]
    public function testReceivesTheWholeObject(User $user): void
    {
        $this->assertSame(1, $user->id);
        $this->assertSame('admin@example.com', $user->email);
        $this->assertNotFalse(getenv('PHPUNIT_WORKER_ID'));
    }
}
