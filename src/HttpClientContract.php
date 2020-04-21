<?php


namespace Buzz\LaravelHCaptcha;


interface HttpClientContract
{
    /**
     * Send post request and return array
     *
     * @param string $uri URI object or string.
     * @param array $options Request options to apply. See \GuzzleHttp\RequestOptions.
     *
     * @return array
     */
    public function post($uri = '', array $options = []);
}
