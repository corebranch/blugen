<?php

namespace Blugen\Tests\Unit\Service\Xrpc\Encoder;

use Blugen\Service\Xrpc\CallableInterface;
use Blugen\Service\Xrpc\Encoder\Encoder;
use Blugen\Service\Xrpc\Encoder\Encoders\EncoderInterface;
use Blugen\Service\Xrpc\Encoder\Exceptions\EncoderException;
use Blugen\Service\Xrpc\Encoder\Factory;
use Blugen\Tests\TestCase;

class EncoderTest extends TestCase
{
    public function test_it_constructs_with_callable_and_factory(): void
    {
        $callable = $this->createMock(CallableInterface::class);
        $factory = $this->createMock(Factory::class);
        
        $encoder = new Encoder($callable, $factory);
        
        $this->assertInstanceOf(Encoder::class, $encoder);
    }

    public function test_it_constructs_with_callable_only(): void
    {
        $callable = $this->createMock(CallableInterface::class);
        
        $encoder = new Encoder($callable);
        
        $this->assertInstanceOf(Encoder::class, $encoder);
    }

    public function test_it_encodes_successfully(): void
    {
        $callable = $this->createMock(CallableInterface::class);
        $factory = $this->createMock(Factory::class);
        $encoderMock = $this->createMock(EncoderInterface::class);
        
        $expectedResult = 'encoded_data';
        
        $factory->expects($this->once())
            ->method('create')
            ->willReturn($encoderMock);
        
        $encoderMock->expects($this->once())
            ->method('encode')
            ->willReturn($expectedResult);
        
        $encoder = new Encoder($callable, $factory);
        $result = $encoder->encode();
        
        $this->assertEquals($expectedResult, $result);
    }

    public function test_it_wraps_exceptions_in_encoder_exception(): void
    {
        $callable = $this->createMock(CallableInterface::class);
        $factory = $this->createMock(Factory::class);
        $encoderMock = $this->createMock(EncoderInterface::class);
        
        $originalException = new \RuntimeException('Original error', 123);
        
        $factory->expects($this->once())
            ->method('create')
            ->willReturn($encoderMock);
        
        $encoderMock->expects($this->once())
            ->method('encode')
            ->willThrowException($originalException);
        
        $encoder = new Encoder($callable, $factory);
        
        $this->expectException(EncoderException::class);
        $this->expectExceptionMessage('Original error');
        $this->expectExceptionCode(123);
        
        $encoder->encode();
    }

    public function test_encoder_exception_has_previous_exception(): void
    {
        $callable = $this->createMock(CallableInterface::class);
        $factory = $this->createMock(Factory::class);
        $encoderMock = $this->createMock(EncoderInterface::class);
        
        $originalException = new \RuntimeException('Original error');
        
        $factory->method('create')->willReturn($encoderMock);
        $encoderMock->method('encode')->willThrowException($originalException);
        
        $encoder = new Encoder($callable, $factory);
        
        try {
            $encoder->encode();
            $this->fail('Expected EncoderException to be thrown');
        } catch (EncoderException $e) {
            $this->assertSame($originalException, $e->getPrevious());
        }
    }

    public function test_it_wraps_factory_create_exceptions(): void
    {
        $callable = $this->createMock(CallableInterface::class);
        $factory = $this->createMock(Factory::class);
        
        $originalException = new \InvalidArgumentException('Factory error', 456);
        
        $factory->expects($this->once())
            ->method('create')
            ->willThrowException($originalException);
        
        $encoder = new Encoder($callable, $factory);
        
        $this->expectException(EncoderException::class);
        $this->expectExceptionMessage('Factory error');
        $this->expectExceptionCode(456);
        
        $encoder->encode();
    }
}
