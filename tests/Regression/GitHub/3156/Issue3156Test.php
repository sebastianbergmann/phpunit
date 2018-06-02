<?php
declare(strict_types = 1);

namespace Test;
use PHPUnit\Framework\TestCase;
use \stdClass;

class TestTest extends TestCase
{

    public function testConstants(): stdClass
    {
        $this->assertStringEndsWith('/', "/");
        return new stdClass();
    }

    public function dataSelectOperatorsProvider(): array
    {
        return [
            ["1"],
            ["2"]
        ];
    }

    /**
     * @depends testConstants
     * @dataProvider dataSelectOperatorsProvider
     *
     * @return void
     */
    public function testDependsRequire(string $val, stdClass $obj): void
    {
        $this->assertStringEndsWith('/', "/");
    }
}
