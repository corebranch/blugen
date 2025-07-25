<?php

namespace Blugen\Tests\Unit\Service\Xrpc;

use Blugen\Service\Xrpc\HttpClientFactory;
use Blugen\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientFactoryTest extends TestCase
{
    public function test_create_with_default_options(): void
    {
        $client = HttpClientFactory::create();

        $this->assertInstanceOf(HttpClientInterface::class, $client);
    }

    public function test_create_with_custom_base_url(): void
    {
        $baseUrl = 'https://custom.atproto.com';
        $client = HttpClientFactory::create($baseUrl);

        $this->assertInstanceOf(HttpClientInterface::class, $client);
    }

    public function test_create_with_default_options_array(): void
    {
        $defaultOptions = [
            'timeout' => 30,
            'max_redirects' => 3
        ];

        $client = HttpClientFactory::create(null, $defaultOptions);

        $this->assertInstanceOf(HttpClientInterface::class, $client);
    }

    public function test_create_with_custom_base_url_and_options(): void
    {
        $baseUrl = 'https://custom.atproto.com';
        $defaultOptions = [
            'timeout' => 60,
            'headers' => ['User-Agent' => 'TestClient/1.0']
        ];

        $client = HttpClientFactory::create($baseUrl, $defaultOptions);

        $this->assertInstanceOf(HttpClientInterface::class, $client);
    }

    public function test_with_auth_token_adds_authorization_header(): void
    {
        /** @var HttpClientInterface|MockObject $mockClient */
        $mockClient = $this->createMock(HttpClientInterface::class);
        $token = 'test-bearer-token';

        $expectedOptions = [
            'headers' => ['Authorization' => 'Bearer test-bearer-token']
        ];

        $mockClient
            ->expects($this->once())
            ->method('withOptions')
            ->with($expectedOptions)
            ->willReturn($mockClient);

        $result = HttpClientFactory::withAuthToken($mockClient, $token);

        $this->assertSame($mockClient, $result);
    }

    public function test_with_auth_token_preserves_existing_client_state(): void
    {
        /** @var HttpClientInterface|MockObject $originalClient */
        $originalClient = $this->createMock(HttpClientInterface::class);
        
        /** @var HttpClientInterface|MockObject $newClient */
        $newClient = $this->createMock(HttpClientInterface::class);
        
        $token = 'auth-token-123';

        $originalClient
            ->expects($this->once())
            ->method('withOptions')
            ->with(['headers' => ['Authorization' => 'Bearer auth-token-123']])
            ->willReturn($newClient);

        $result = HttpClientFactory::withAuthToken($originalClient, $token);

        $this->assertSame($newClient, $result);
        $this->assertNotSame($originalClient, $result);
    }

    public function test_with_auth_token_handles_empty_token(): void
    {
        /** @var HttpClientInterface|MockObject $mockClient */
        $mockClient = $this->createMock(HttpClientInterface::class);
        $token = '';

        $expectedOptions = [
            'headers' => ['Authorization' => 'Bearer ']
        ];

        $mockClient
            ->expects($this->once())
            ->method('withOptions')
            ->with($expectedOptions)
            ->willReturn($mockClient);

        $result = HttpClientFactory::withAuthToken($mockClient, $token);

        $this->assertSame($mockClient, $result);
    }
}
