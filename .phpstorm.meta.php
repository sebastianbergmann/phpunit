<?php

namespace PHPSTORM_META {
    override(
        \PHPUnit\Framework\TestCase::createMock(0),
        map([
            '' => '@|\PHPUnit\Framework\MockObject\MockObject',
        ])
    );
    override(
        \PHPUnit_Framework_TestCase::createMock(0),
        map([
            '' => '@|\PHPUnit_Framework_MockObject_MockObject',
        ])
    );
}
