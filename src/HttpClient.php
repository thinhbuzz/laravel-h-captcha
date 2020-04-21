<?php


namespace Buzz\LaravelHCaptcha;


use Exception;
use GuzzleHttp\Client;

class HttpClient implements HttpClientContract
{
    /**
     * Name of callback function
     *
     * @var Client $client
     */
    public $client;

    public function __construct()
    {
        $this->initialHttpClient();
    }

    /**
     * Initial http client via GuzzleHttp\Client
     */
    protected function initialHttpClient()
    {
        $this->client = new Client();
    }

    /**
     * Send post request and return array
     *
     * @param string $uri URI object or string.
     * @param array $options Request options to apply. See \GuzzleHttp\RequestOptions.
     *
     * @return array
     */
    public function post($uri = '', array $options = [])
    {
        try {
            $response = $this->client->request('POST', $uri, $options);

            return json_decode($response->getBody(), true);
        } catch (Exception $exception) {

            return [];
        }
    }
}
