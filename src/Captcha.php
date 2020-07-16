<?php


namespace Buzz\LaravelHCaptcha;


use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;

class Captcha
{
    const VERIFY_API_ENDPOINT = 'https://hcaptcha.com/siteverify';
    const CAPTCHA_CLIENT_API = 'https://hcaptcha.com/1/api.js';

    /**
     * Name of callback function
     *
     * @var HttpClientContract $httpClient
     */
    public $httpClient;

    /**
     * Name of callback function
     *
     * @var string $callbackName
     */
    protected $callbackName = 'buzzHCaptchaOnLoadCallback';

    /**
     * Name of widget ids
     *
     * @var string $widgetIdName
     */
    protected $widgetIdName = 'buzzHCaptchaWidgetIds';

    /**
     * Prefix for captcha id
     *
     * @var string $captchaIdPrefix
     */
    protected $captchaIdPrefix = 'buzzHCaptchaId_';

    /**
     * Name of js variable
     *
     * @var string $jsVariableName
     */
    protected $jsVariableName = 'hcaptcha';


    /**
     * Each captcha attributes in multiple mode
     *
     * @var array $multipleCaptchaData
     */
    protected $multipleCaptchaData = [];

    /**
     * @var Repository $config
     */
    protected $config;

    /**
     * @var Application $app
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $this->app['config'];
        $this->initialHttpClient();
    }

    /**
     * Initial http client via GuzzleHttp\Client
     */
    protected function initialHttpClient()
    {
        $httpClientClass = $this->config->get('captcha.http_client');

        $this->httpClient = $this->app->make($httpClientClass);
    }

    /**
     * Create captcha html element
     *
     * @param array $attributes
     * @param array $options
     *
     * @return string
     */
    public function display($attributes = [], $options = [])
    {
        $isMultiple = (bool)$this->optionOrConfig($options, 'options.multiple');
        if (!array_key_exists('id', $attributes)) {
            $attributes['id'] = $this->randomCaptchaId();
        }
        $html = '';
        if (!$isMultiple && Arr::get($attributes, 'add-js', true)) {
            $html .= '<script src="' . $this->getJsLink($options) . '" async defer></script>';
        }
        unset($attributes['add-js']);
        $attributeOptions = $this->optionOrConfig($options, 'attributes');
        if (!empty($attributeOptions)) {
            $attributes = array_merge($attributeOptions, $attributes);
        }
        if ($isMultiple) {
            array_push( $this->multipleCaptchaData, [$attributes, $options] );
        } else {
            $attributes['data-sitekey'] = $this->optionOrConfig($options, 'sitekey');
        }

        return $html . '<div class="h-captcha"' . $this->buildAttributes($attributes) . '></div>';
    }

    /**
     * Random id unique
     *
     * @return string
     */
    protected function randomCaptchaId()
    {
        return $this->captchaIdPrefix . md5(uniqid(rand(), true));
    }

    /**
     * Create javascript api link with language
     *
     * @param array $options
     *
     * @return string
     */
    public function getJsLink($options = [])
    {
        $query = [];
        if ($this->optionOrConfig($options, 'options.multiple')) {
            $query = [
                'onload' => $this->callbackName,
                'render' => 'explicit',
            ];
        }
        $lang = $this->optionOrConfig($options, 'options.lang');
        if ($lang) {
            $query['hl'] = $lang;
        }

        return static::CAPTCHA_CLIENT_API . '?' . http_build_query($query);
    }

    /**
     * Create captcha element with attributes
     *
     * @param array $attributes
     *
     * @return string
     */
    protected function buildAttributes(array $attributes)
    {
        $html = [];
        foreach ($attributes as $key => $value) {
            $html[] = $key . '="' . $value . '"';
        }

        return count($html) ? ' ' . implode(' ', $html) : '';
    }

    /**
     * Display multiple captcha on page
     *
     * @param array $globalOptions
     *
     * @return string
     */
    public function displayMultiple($globalOptions = [])
    {
        if ( ! $this->optionOrConfig( $globalOptions, 'options.multiple' ) ) {
            return '';
        }
        $renderHtml = '';
        foreach ($this->multipleCaptchaData as $multipleCaptchaData) {
            $attributes = $multipleCaptchaData[0];
            $options = array_merge( $globalOptions, $multipleCaptchaData[1] );
            $renderHtml .= "{$this->widgetIdName}[\"{$attributes['id']}\"]={$this->buildCaptchaHtml($attributes, $options)}";
        }

        return "<script type=\"text/javascript\">var {$this->widgetIdName}={};var {$this->callbackName}=function(){{$renderHtml}};</script>";
    }

    /**
     * @param array $options
     * @param array $attributes
     *
     * @return string
     * @internal param null $lang
     */
    public function displayJs($options = [], $attributes = ['async', 'defer'])
    {
        return '<script src="' . htmlspecialchars($this->getJsLink($options)) . '" ' . implode(' ', $attributes) . '></script>';
    }

    /**
     * @param boolean $multiple
     */
    public function multiple($multiple = true)
    {
        $this->config->set('captcha.options.multiple', $multiple);
    }

    /**
     * @param array $options
     */
    public function setOptions($options = [])
    {
        $this->config->set('captcha.options', $options);
    }


    /**
     * @param string $response
     * @param string $clientIp
     * @param array $options
     *
     * @return bool
     */
    public function verify($response, $clientIp = null, $options = [])
    {
        $secret = $this->optionOrConfig($options, 'secret');
        $response = $this->httpClient->post(static::VERIFY_API_ENDPOINT, [
            'form_params' => [
                'secret' => $secret,
                'response' => $response,
                'remoteip' => $clientIp
            ]
        ]);
        return array_key_exists('success', $response) ? $response['success'] : false;
    }

    /**
     * Build captcha by attributes
     *
     * @param array $captchaAttribute
     * @param array $options
     *
     * @return string
     */
    protected function buildCaptchaHtml($captchaAttribute = [], $options = [])
    {
        $options = array_merge(
            ['sitekey' => $this->optionOrConfig($options, 'sitekey')],
            $this->optionOrConfig($options, 'attributes', [])
        );
        foreach ($captchaAttribute as $key => $value) {
            $options[str_replace('data-', '', $key)] = $value;
        }
        $options = json_encode($options);

        return "{$this->jsVariableName}.render('{$captchaAttribute['id']}',{$options});";
    }

    /**
     * @param array $options
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function optionOrConfig($options = [], $key = '', $default = null)
    {
        return Arr::get($options, str_replace('options.', '', $key), $this->config->get('captcha.' . $key, $default));
    }

    /**
     * @return string
     */
    public function getWidgetIdName(): string
    {
        return $this->widgetIdName;
    }

    /**
     * @return string
     */
    public function getJsVariableName(): string
    {
        return $this->jsVariableName;
    }
}
