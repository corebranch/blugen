<?php

namespace Blugen\Tests\Unit\Service\Xrpc\Encoder;

use Blugen\Service\Lexicon\ParamsInterface;
use Blugen\Service\Lexicon\QueryInterface;
use Blugen\Service\Xrpc\Encoder\QueryDataAdapter;
use Blugen\Tests\TestCase;
use BlugenGenerator\Com\Atproto\Server\GetSession;

class QueryDataAdapterTest extends TestCase
{
    public function test_get_data_that_returns_expected_class(): void
    {
        $mockParams = $this->createMock(ParamsInterface::class);
        $mockQuery = $this->createMock(QueryInterface::class);

        $mockQuery->expects($this->once())
            ->method('getParams')
            ->willReturn($mockParams);

        $this->assertSame($mockParams, (new QueryDataAdapter($mockQuery))->getData());
    }
}
