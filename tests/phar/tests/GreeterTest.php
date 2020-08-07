<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

/**
 * @covers Greeter
 */
final class GreeterTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/../src/Greeter.php';
    }

    /**
     * @dataProvider provideBlankOrEmptyName
     *
     * @param string $name
     */
    public function testGreetRejectsBlankOrEmptyName(string $name): void
    {
        $greeter = new Greeter();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name should not be a blank or empty string.');

        $greeter->greet($name);
    }

    /**
     * @return Generator<string, array{0: string}>
     */
    public function provideBlankOrEmptyName(): \Generator
    {
        yield 'blank' => [' '];
        yield 'empty' => [''];
    }

    public function testGreetReturnsGreeting()
    {
        $name = 'Sebastian';

        $greeter = new Greeter();

        $greeting = $greeter->greet($name);

        self::assertSame("Hello, ${name}!", $greeting);
    }
}
