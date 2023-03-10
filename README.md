# [hCaptcha](https://www.hcaptcha.com) for Laravel

## Features

- [x]  Support Laravel 5/6/7/8/9/10

- [x] Multiple captcha on page

- [x] Reset captcha

- [x] Auto discover service provider

- [x] Custom request method

- [x] Using difference key

- [x] Dynamic options on runtime

## Installation

Add the following line to the `require` section of `composer.json`:

```json
{
    "require": {
        "buzz/laravel-h-captcha": "1.*"
    }
}
```

OR

Require this package with composer:
```
composer require buzz/laravel-h-captcha
```

Update your packages with ```composer update``` or install with ```composer install```.

## Setup

> Has support auto discover for Laravel >=5.5

Add ServiceProvider to the `providers` array in `config/app.php`.

```
'Buzz\LaravelHCaptcha\CaptchaServiceProvider',
```

## Publish Config

```
php artisan vendor:publish --provider="Buzz\LaravelHCaptcha\CaptchaServiceProvider"
```

### Custom http client

Edit ``http_client`` in the ``config/captcha.php`` config

file ``config/captcha.php``

```php
<?php
/*
 * Secret key and Site key get on https://dashboard.hcaptcha.com/sites
 * */
return [
    'secret' => env('CAPTCHA_SECRET', 'default_secret'),
    'sitekey' => env('CAPTCHA_SITEKEY', 'default_sitekey'),
    // \GuzzleHttp\Client used is the default client
    'http_client' => \Buzz\LaravelHCaptcha\HttpClient::class,
    'options' => [
        'multiple' => false,
        'lang' => app()->getLocale(),
    ],
    'attributes' => [
        'theme' => 'light'
    ],
];
```

## Configuration

Add `CAPTCHA_SECRET` and `CAPTCHA_SITEKEY` to **.env** file:

```
CAPTCHA_SECRET=[secret-key]
CAPTCHA_SITEKEY=[site-key]
```

## Usage

### View example
> Get examples in [examples repo](https://github.com/thinhbuzz/laravel-h-captcha-examples/tree/master/resources/views)


### Display hCaptcha

```php
{!! app('captcha')->display($attributes) !!}
```

OR use Facade: add `'Captcha' => '\Buzz\LaravelHCaptcha\CaptchaFacade',` to the `aliases` array in `config/app.php` and in template use:

```php
{!! Captcha::display($attributes) !!}
```
OR use Form

```php
{!! Form::captcha($attributes) !!}
```
With custom language support:
```php
{!! app('captcha')->display($attributes = [], $options = ['lang'=> 'vi']) !!}
```

With

```php
// element attributes
$attributes = [
    'data-theme' => 'dark',
    'data-type' => 'audio',
];
```
```php
// package options
$options = [
    'data-theme' => 'dark',
    'data-type'	=> 'audio',
];
```

More information on [hCaptcha document](https://docs.hcaptcha.com/)
> Please help me write readme for this content

### Validation

Add `'h-captcha-response' => 'required|captcha'` to rules array.

```php
use Validator;
use Illuminate\Support\Facades\Input;

$validate = Validator::make(Input::all(), [
    'h-captcha-response' => 'required|captcha'
]);
```

### Testing

When using the Laravel Testing functionality, you will need to mock out the response for the captcha form element.
For any form tests involving the captcha, you can then mock the facade behaviour:

```php
// Prevent validation error on captcha
        CaptchaFacade::shouldReceive('verify')
            ->andReturn(true);
            
// Provide hidden input for your 'required' validation
        CaptchaFacade::shouldReceive('display')
            ->andReturn('<input type="hidden" name="h-captcha-response" value="1" />');
            
// Add these when testing multiple captchas on a single page
        CaptchaFacade::shouldReceive('displayJs');
        CaptchaFacade::shouldReceive('displayMultiple');
        CaptchaFacade::shouldReceive('multiple');
```

## Contribute

https://github.com/thinhbuzz/laravel-h-captcha/pulls
