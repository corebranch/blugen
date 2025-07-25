<?php

namespace Blugen\Service\Xrpc;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientFactory
{
    public static function create(?string $baseUrl = null, ?array $defaultOptions = null): HttpClientInterface
    {
        $options = array_merge(
            $defaultOptions ?? config()->get('client.default_options', []),
            [
                'base_uri' => sprintf(
                    "%s/xrpc/",
                    $baseUrl ?? config()->get('client.default_options.base_uri', '')
                )
            ]
        );

        return HttpClient::create()->withOptions($options);
    }

    public static function withAuthToken(HttpClientInterface $client, string $token): HttpClientInterface
    {
        return $client->withOptions([
            'headers' => ['Authorization' => "Bearer $token"]
        ]);
    }
}