<?php

namespace App\Services\Providers;

trait CurlTrait
{

    protected $client;

    public function getClient(array $config = [])
    {
        $base   = [
            //'headers' => [
            //    'Accept'       => ' application/json',
            //    'Content-Type' => 'application/json; charset=utf-8',
            //],
        ];
        $config = array_merge($base, $config);

        $this->client = new \GuzzleHttp\Client($config);

        return $this;
    }

    protected function request($url, $params = [], $method = 'get')
    {
        switch (strtolower($method)) {
            case 'post':
                $params   = [
                    'form_params' => $params,
                ];
                $response = $this->client->post($url, $params);
                break;
            case 'put':
                $params   = [
                    'form_params' => $params,
                ];
                $response = $this->client->put($url, $params);
                break;
            case 'delete':
                $response = $this->client->delete($url);
                break;
            default:
                $params   = [
                    'query' => $params,
                ];
                $response = $this->client->get($url, $params);
        }
        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }
}
