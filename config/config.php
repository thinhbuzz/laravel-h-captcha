<?php
/*
 * Secret key and Site key get on https://dashboard.hcaptcha.com/sites
 * */
return [
    'secret' => env('CAPTCHA_SECRET', 'default_secret'),
    'sitekey' => env('CAPTCHA_SITEKEY', 'default_sitekey'),
    'http_client' => \Buzz\LaravelHCaptcha\HttpClient::class,
    'options' => [
        'multiple' => false,
        'lang' => app()->getLocale(),
    ],
    'attributes' => [
        'theme' => 'light'
    ],
];
