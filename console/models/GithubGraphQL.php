<?php

namespace console\models;

use GuzzleHttp\Client;

class GithubGraphQL
{

    public static function getGraphQL($query)
    {
        $client = new Client();
        $response = $client->post('https://api.github.com/graphql', [
            'headers' => [
                'Authorization' => 'Bearer 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',
            ],
            'json' => [
                'query' => $query,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}