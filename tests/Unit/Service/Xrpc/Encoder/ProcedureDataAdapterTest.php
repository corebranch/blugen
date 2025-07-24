<?php

namespace Blugen\Tests\Unit\Service\Xrpc\Encoder;

use Blugen\Service\Lexicon\InputInterface;
use Blugen\Service\Xrpc\Encoder\ProcedureDataAdapter;
use Blugen\Service\Lexicon\ProcedureInterface;
use Blugen\Tests\TestCase;

class ProcedureDataAdapterTest extends TestCase
{
    public function test_get_data_that_returns_expected_class(): void
    {
        $mockInput = $this->createMock(InputInterface::class);
        $mockProcedure = $this->createMock(ProcedureInterface::class);

        $mockProcedure->expects($this->once())
            ->method('getSchema')
            ->willReturn($mockInput);

        $this->assertSame($mockInput, (new ProcedureDataAdapter($mockProcedure))->getData());
    }
}
