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
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Client implements ClientInterface
{
    private array $session = [];

    public function __construct(
        ?string                                                    $baseUrl = null,
        private ?\Symfony\Contracts\HttpClient\HttpClientInterface $httpClient = null
    )
    {
        $this->httpClient = $httpClient ?? HttpClient::create()->withOptions(array_merge(
            config()->get('client.default_options'),
            ['base_uri' => sprintf(
                "%s/xrpc/",
                $baseUrl ?? config()->get('client.default_options.base_uri')
            )]
        ));
    }

    public function login(string $handle, string $password, ?array $session = null): array
    {
        if (is_array($session) && isset($session['accessJwt']) && isset($session['refreshJwt'])) {
            $this->httpClient = $this->httpClient->withOptions([
                'headers' => ['Authorization' => "Bearer $session[accessJwt]"]
            ]);

            try {
                $validatedSession = $this->call(
                    nsid('com.atproto.server.getSession'),
                    new GetSessionParams()
                )->toArray();

                $this->httpClient = $this->httpClient->withOptions([
                    'headers' => ['Authorization' => "Bearer $validatedSession[accessJwt]"]
                ]);

                $this->session = $session;
            } catch (ExpiredToken $e) {
                $this->httpClient = $this->httpClient->withOptions([
                    'headers' => ['Authorization' => "Bearer $session[refreshJwt]"]
                ]);

                $renewedSession = $this->call(
                    nsid('com.atproto.server.refreshSession'),
                    new RefreshSessionInput()
                );

                $this->httpClient = $this->httpClient->withOptions([
                    'headers' => ['Authorization' => "Bearer $renewedSession[accessJwt]"]
                ]);

                $this->session = $this->call(
                    nsid('com.atproto.server.getSession'),
                    new GetSessionParams()
                )->toArray();
            } catch (ExpiredToken $e) {
                $createdSession = $this->call(
                    \nsid('com.atproto.server.createSession'),
                    (new CreateSessionInput())->setIdentifier($handle)->setPassword($password)
                );

                $this->httpClient = $this->httpClient->withOptions([
                    'headers' => ['Authorization' => "Bearer $createdSession[accessJwt]"]
                ]);

                $this->session = $createdSession->toArray();
            }

            return $this->session;
        }

        return $this->session = $this->call(
            nsid('com.atproto.server.createSession'),
            (new CreateSessionInput())->setIdentifier($handle)->setPassword($password)
        )->toArray();
    }

    /**
     * @param Nsid $nsid
     * @param ParamsInterface|InputInterface $parameter
     * @return ResponseInterface
     * @throws XrpcException
     */
    public function call(Nsid $nsid, ParamsInterface|InputInterface|null $parameter = null): ResponseInterface
    {
        $callable = $this->callable($nsid, $parameter);

        try {
            // trigger exception if exist
            $response = $this->httpClient->request(
                $callable->method(),
                $callable->path(),
                $callable->options()
            );
        } catch (ClientExceptionInterface $e) {
            $errorResponse = $e->getResponse()->toArray();

            $errorParameters = [
                $errorResponse['message'] ?? 'Bad Request',
                $e->getResponse()->getStatusCode() ?? 400,
                $e
            ];

            $exceptionClass = $this->getValidExceptionClass($errorResponse['error'] ?? '');
            throw new $exceptionClass(...$errorParameters);
        } catch (\Throwable $e) {
            throw new XrpcException($e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    private function callable(Nsid $nsid, ParamsInterface|InputInterface|null $parameter): CallableInterface
    {
        $definition = Definition::fromNsid($nsid);
        [$namespace, $className] = NamespaceResolver::namespace($definition->lexicon(), $definition);

        $fullClassName = "\\$namespace\\$className";

        if (!class_exists($fullClassName)) {
            throw new \InvalidArgumentException("Callable class not found: $fullClassName");
        }

        if (!is_subclass_of($fullClassName, CallableInterface::class)) {
            throw new \InvalidArgumentException("Class does not implement CallableInterface: $fullClassName");
        }

        return new $fullClassName($parameter);
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
