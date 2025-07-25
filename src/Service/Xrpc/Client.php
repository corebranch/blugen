<?php

namespace Blugen\Service\Xrpc;

use Blugen\Service\Lexicon\InputInterface;
use Blugen\Service\Lexicon\ParamsInterface;
use Blugen\Service\Lexicon\V1\Definition;
use Blugen\Service\Lexicon\V1\Nsid;
use Blugen\Service\Lexicon\V1\Resolver\NamespaceResolver;
use Blugen\Service\Xrpc\Exception\ExpiredToken;
use Blugen\Service\Xrpc\Exception\XrpcException;
use BlugenGenerator\Com\Atproto\Server\CreateSessionInput;
use BlugenGenerator\Com\Atproto\Server\GetSessionParams;
use BlugenGenerator\Com\Atproto\Server\RefreshSessionInput;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class Client implements ClientInterface
{
    private SessionManager $sessionManager;
    private HttpClientInterface $httpClient;

    public function __construct(
        ?string $baseUrl = null,
        ?HttpClientInterface $httpClient = null
    )
    {
        $this->sessionManager = new SessionManager();
        $this->httpClient = $httpClient ?? HttpClientFactory::create($baseUrl);
    }

    /**
     * @throws XrpcException
     */
    public function login(string $handle, string $password, ?array $session = null): array
    {
        if ($this->hasValidExistingSession($session)) {
            return $this->loginWithExistingSession($handle, $password, $session);
        }

        return $this->createNewSession($handle, $password);
    }

    public function getSession(): array
    {
        return $this->sessionManager->getSession();
    }

    private function hasValidExistingSession(?array $session): bool
    {
        return is_array($session) && isset($session['accessJwt'], $session['refreshJwt']);
    }

    /**
     * @throws XrpcException
     */
    private function loginWithExistingSession(string $handle, string $password, array $session): array
    {
        $this->sessionManager->setSession($session);
        $this->httpClient = HttpClientFactory::withAuthToken($this->httpClient, $session['accessJwt']);

        try {
            return $this->validateCurrentSession();
        } catch (ExpiredToken) {
            return $this->handleExpiredAccessToken($handle, $password);
        }
    }

    /**
     * @throws XrpcException
     */
    private function validateCurrentSession(): array
    {
        $validatedSession = $this->call(
            nsid('com.atproto.server.getSession'),
            new GetSessionParams()
        )->toArray();

        $this->sessionManager->updateFromResponse($validatedSession);
        $this->httpClient = HttpClientFactory::withAuthToken($this->httpClient, $validatedSession['accessJwt']);

        return $this->sessionManager->getSession();
    }

    /**
     * @throws XrpcException
     */
    private function handleExpiredAccessToken(string $handle, string $password): array
    {
        try {
            return $this->refreshSession();
        } catch (ExpiredToken) {
            return $this->createNewSession($handle, $password);
        }
    }

    /**
     * @throws XrpcException
     * @throws ExpiredToken
     */
    private function refreshSession(): array
    {
        $refreshToken = $this->sessionManager->getRefreshToken();
        $this->httpClient = HttpClientFactory::withAuthToken($this->httpClient, $refreshToken);

        $renewedSession = $this->call(
            nsid('com.atproto.server.refreshSession'),
            new RefreshSessionInput()
        )->toArray();

        $this->sessionManager->updateFromResponse($renewedSession);
        $this->httpClient = HttpClientFactory::withAuthToken($this->httpClient, $renewedSession['accessJwt']);

        return $this->validateCurrentSession();
    }

    /**
     * @throws XrpcException
     */
    private function createNewSession(string $handle, string $password): array
    {
        $createdSession = $this->call(
            nsid('com.atproto.server.createSession'),
            (new CreateSessionInput())->setIdentifier($handle)->setPassword($password)
        )->toArray();

        $this->sessionManager->setSession($createdSession);
        $this->httpClient = HttpClientFactory::withAuthToken($this->httpClient, $createdSession['accessJwt']);

        return $this->sessionManager->getSession();
    }

    /**
     * @throws XrpcException
     */
    public function call(Nsid $nsid, ParamsInterface|InputInterface|null $parameter = null): ResponseInterface
    {
        $callable = $this->createCallable($nsid, $parameter);

        try {
            return $this->httpClient->request(
                $callable->method(),
                $callable->path(),
                $callable->options()
            );
        } catch (ClientExceptionInterface|TransportExceptionInterface $e) {
            throw $this->handleClientException($e);
        } catch (Throwable $e) {
            throw new XrpcException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function createCallable(Nsid $nsid, ParamsInterface|InputInterface|null $parameter): CallableInterface
    {
        $definition = Definition::fromNsid($nsid);
        [$namespace, $className] = NamespaceResolver::namespace($definition->lexicon(), $definition);

        $fullClassName = "\\$namespace\\$className";

        $this->validateCallableClass($fullClassName);

        return new $fullClassName($parameter);
    }

    private function validateCallableClass(string $fullClassName): void
    {
        if (!class_exists($fullClassName)) {
            throw new InvalidArgumentException("Callable class not found: $fullClassName");
        }

        if (!is_subclass_of($fullClassName, CallableInterface::class)) {
            throw new InvalidArgumentException("Class does not implement CallableInterface: $fullClassName");
        }
    }

    private function handleClientException(ClientExceptionInterface|TransportExceptionInterface $e): XrpcException
    {
        if ($e instanceof TransportExceptionInterface) {
            return new XrpcException($e->getMessage(), $e->getCode(), $e);
        }

        $errorResponse = $e->getResponse()->toArray();
        $errorMessage = $errorResponse['message'] ?? 'Bad Request';
        $statusCode = $e->getResponse()->getStatusCode() ?? 400;
        $errorType = $errorResponse['error'] ?? '';

        $exceptionClass = $this->getValidExceptionClass($errorType);
        
        return new $exceptionClass($errorMessage, $statusCode, $e);
    }

    private function getValidExceptionClass(string $errorType): string
    {
        if (empty($errorType)) {
            return XrpcException::class;
        }

        $exceptionClass = "\\Blugen\\Service\\Xrpc\\Exception\\$errorType";

        if (class_exists($exceptionClass) && is_subclass_of($exceptionClass, XrpcException::class)) {
            return $exceptionClass;
        }

        return XrpcException::class;
    }
}
