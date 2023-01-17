<?php

namespace Tests\Unit;

use Orchestra\Testbench\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected function assertException($exceptionName, $exceptionMessage = '', $exceptionCode = 0)
    {
        if (method_exists(parent::class, 'expectException')) {
            parent::expectException($exceptionName);
            parent::expectExceptionMessage($exceptionMessage);
            parent::expectExceptionCode($exceptionCode);
        } else {
            $this->setExpectedException($exceptionName, $exceptionMessage, $exceptionCode);
        }
    }
}
