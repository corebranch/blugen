<?php

return [
    'client' => [
        'default_options' => [
            'base_uri' => 'https://bsky.social',
            'headers' => [
                'User-Agent' => 'Blugen XRPC Client',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]
    ],

    'lexicons' => [
        'source' => __DIR__ . "/../atproto/lexicons",
    ],

    'output' => [
        'base_namespace' => "BlugenGenerator\\",
    ],
];
