<?php

namespace Blugen\Tests\Unit\Service\Xrpc;

use Blugen\Enum\ClassNameSuffix;
use Blugen\Service\Lexicon\InputInterface;
use Blugen\Service\Lexicon\ParamsInterface;
use Blugen\Service\Lexicon\V1\Nsid;
use Blugen\Service\Xrpc\CallableInterface;
use Blugen\Service\Xrpc\Client;
use Blugen\Service\Xrpc\Exception\ExpiredToken;
use Blugen\Service\Xrpc\Exception\XrpcException;
use Blugen\Tests\TestCase;
use BlugenGenerator\Com\Atproto\Server\CreateSessionInput;
use BlugenGenerator\Com\Atproto\Server\GetSessionParams;
use BlugenGenerator\Com\Atproto\Server\RefreshSessionInput;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TestableClient extends Client
{
    private ?CallableInterface $mockCallable;

    public function __construct(?string $baseUrl = null, ?HttpClientInterface $httpClient = null, ?CallableInterface $mockCallable = null)
    {
        parent::__construct($baseUrl, $httpClient);
        $this->mockCallable = $mockCallable;
    }

    public function createCallable(Nsid $nsid, ParamsInterface|InputInterface|null $parameter = null): CallableInterface
    {
        if ($this->mockCallable) {
            return $this->mockCallable;
        }
        return parent::createCallable($nsid, $parameter);
    }

    protected function createParameterClass(Nsid $nsid, ClassNameSuffix $suffix): ParamsInterface|InputInterface
    {
        // Return hardcoded classes to avoid lexicon file dependencies in tests
        $nsidString = $nsid->full();
        
        return match ([$nsidString, $suffix]) {
            ['com.atproto.server.getSession', ClassNameSuffix::PARAMS] => new GetSessionParams(),
            ['com.atproto.server.refreshSession', ClassNameSuffix::INPUT] => new RefreshSessionInput(),
            ['com.atproto.server.createSession', ClassNameSuffix::INPUT] => new CreateSessionInput(),
            default => parent::createParameterClass($nsid, $suffix)
        };
    }
}

class ClientTest extends TestCase
{
    private HttpClientInterface|MockObject $httpClient;
    private TestableClient $client;
    private CallableInterface|MockObject $mockCallable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->mockCallable = $this->createMock(CallableInterface::class);
        $this->client = new TestableClient(null, $this->httpClient, $this->mockCallable);
    }

    public function test_constructor_with_default_http_client(): void
    {
        $client = new Client();
        $this->assertInstanceOf(Client::class, $client);
    }

    public function test_constructor_with_custom_base_url(): void
    {
        $client = new Client('https://example.com');
        $this->assertInstanceOf(Client::class, $client);
    }

    public function test_login_with_new_session(): void
    {
        $handle = 'test.user';
        $password = 'password123';
        
        $expectedSession = [
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token',
            'handle' => $handle
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('toArray')->willReturn($expectedSession);

        $this->mockCallable->expects($this->any())->method('method')->willReturn('POST');
        $this->mockCallable->expects($this->any())->method('path')->willReturn('com.atproto.server.createSession');
        $this->mockCallable->expects($this->any())->method('options')->willReturn([]);

        $this->httpClient->expects($this->once())->method('request')->willReturn($response);
        $this->httpClient->expects($this->once())->method('withOptions')->willReturn($this->httpClient);

        $result = $this->client->login($handle, $password);

        $this->assertEquals($expectedSession, $result);
        $this->assertEquals($expectedSession, $this->client->getSession());
    }

    public function test_login_with_valid_existing_session(): void
    {
        $handle = 'test.user';
        $password = 'password123';
        $existingSession = [
            'accessJwt' => 'existing-access',
            'refreshJwt' => 'existing-refresh'
        ];

        $validatedSession = [
            'accessJwt' => 'validated-access',
            'refreshJwt' => 'validated-refresh',
            'handle' => $handle
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('toArray')->willReturn($validatedSession);

        $this->mockCallable->expects($this->any())->method('method')->willReturn('GET');
        $this->mockCallable->expects($this->any())->method('path')->willReturn('com.atproto.server.getSession');
        $this->mockCallable->expects($this->any())->method('options')->willReturn([]);

        $this->httpClient->expects($this->once())->method('request')->willReturn($response);
        $this->httpClient->expects($this->atLeastOnce())->method('withOptions')->willReturn($this->httpClient);

        $result = $this->client->login($handle, $password, $existingSession);

        $this->assertEquals($validatedSession, $result);
    }

    public function test_call_with_valid_request(): void
    {
        $nsid = nsid('com.atproto.server.getSession');
        $params = new GetSessionParams();

        $response = $this->createMock(ResponseInterface::class);

        $this->mockCallable->expects($this->any())->method('method')->willReturn('GET');
        $this->mockCallable->expects($this->any())->method('path')->willReturn('com.atproto.server.getSession');
        $this->mockCallable->expects($this->any())->method('options')->willReturn([]);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'com.atproto.server.getSession', [])
            ->willReturn($response);

        $result = $this->client->call($nsid, $params);

        $this->assertEquals($response, $result);
    }

    public function test_call_with_client_exception(): void
    {
        $nsid = nsid('com.atproto.server.getSession');
        $params = new GetSessionParams();

        $errorResponse = $this->createMock(ResponseInterface::class);
        $errorResponse->method('toArray')->willReturn([
            'error' => 'ExpiredToken',
            'message' => 'Token has expired'
        ]);
        $errorResponse->method('getStatusCode')->willReturn(401);

        $clientException = $this->createMock(ClientExceptionInterface::class);
        $clientException->method('getResponse')->willReturn($errorResponse);

        $this->mockCallable->expects($this->any())->method('method')->willReturn('GET');
        $this->mockCallable->expects($this->any())->method('path')->willReturn('com.atproto.server.getSession');
        $this->mockCallable->expects($this->any())->method('options')->willReturn([]);

        $this->httpClient->expects($this->once())->method('request')->willThrowException($clientException);

        $this->expectException(ExpiredToken::class);
        $this->expectExceptionMessage('Token has expired');

        $this->client->call($nsid, $params);
    }

    public function test_call_with_transport_exception(): void
    {
        $nsid = nsid('com.atproto.server.getSession');
        $params = new GetSessionParams();

        $this->mockCallable->expects($this->any())->method('method')->willReturn('GET');
        $this->mockCallable->expects($this->any())->method('path')->willReturn('com.atproto.server.getSession');
        $this->mockCallable->expects($this->any())->method('options')->willReturn([]);

        $transportException = new class extends \Exception implements TransportExceptionInterface {
            public function getResponse(): ResponseInterface
            {
                throw new \LogicException('Not implemented');
            }
        };

        $this->httpClient->expects($this->once())->method('request')->willThrowException($transportException);

        $this->expectException(XrpcException::class);

        $this->client->call($nsid, $params);
    }

    public function test_call_with_generic_exception(): void
    {
        $nsid = nsid('com.atproto.server.getSession');
        $params = new GetSessionParams();

        $this->mockCallable->expects($this->any())->method('method')->willReturn('GET');
        $this->mockCallable->expects($this->any())->method('path')->willReturn('com.atproto.server.getSession');
        $this->mockCallable->expects($this->any())->method('options')->willReturn([]);

        $exception = new \RuntimeException('Generic error', 500);

        $this->httpClient->expects($this->once())->method('request')->willThrowException($exception);

        $this->expectException(XrpcException::class);
        $this->expectExceptionMessage('Generic error');

        $this->client->call($nsid, $params);
    }

    public function test_get_session_returns_current_session(): void
    {
        $expectedSession = [
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token'
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('toArray')->willReturn($expectedSession);

        $this->mockCallable->expects($this->any())->method('method')->willReturn('POST');
        $this->mockCallable->expects($this->any())->method('path')->willReturn('com.atproto.server.createSession');
        $this->mockCallable->expects($this->any())->method('options')->willReturn([]);

        $this->httpClient->expects($this->once())->method('request')->willReturn($response);
        $this->httpClient->expects($this->once())->method('withOptions')->willReturn($this->httpClient);

        $this->client->login('test', 'password');
        
        $this->assertEquals($expectedSession, $this->client->getSession());
    }

    public function test_get_session_returns_empty_array_when_no_session(): void
    {
        $this->assertEquals([], $this->client->getSession());
    }
}
