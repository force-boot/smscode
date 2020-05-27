<?php

namespace app\common\controller;

use \League\OAuth2\Client\Provider\GenericProvider;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class AuthLogin
{
    /**
     * 配置信息
     * @var array
     */
    public $config = [
        'clientId' => 'd9ee2ebf-218f-495d-8593-a64f74ca3484',
        'clientSecret' => '1PFio~2oIYv6C2Tw2-Ejbdnz53~HWJeq-4',
        'redirectUri' => 'https://sms.mitomos.com.cn/auth',
        'urlAuthorize' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
        'urlAccessToken' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
        'urlResourceOwnerDetails' => '',
        'scopes' => 'openid profile offline_access user.read calendars.read'
    ];

    /**
     * 保存实例
     * @var object
     */
    public $provider;

    /**
     * AuthLogin constructor.
     */
    public function __construct()
    {
        $this->provider = new GenericProvider($this->config);
    }

    /**
     * 获取登录地址
     * @return string
     */
    public function getLoginUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        session('state', $this->provider->getState());
        return $url;
    }

    /**
     * 验证参数合法性
     * @param string $state
     * @return bool
     */
    public function checkState($state)
    {
        if (!session('?state') || session('state') != $state) {
            return false;
        }else{
            return true;
        }
    }

    /**
     * 获取accessToken
     * @param string $authCode
     * @param object
     * @return \League\OAuth2\Client\Token\AccessTokenInterface
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function getAccessToken(string $authCode)
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $authCode
        ]);
    }

    /**
     * 获取登录用户信息
     * @param object $accessToken
     * @return mixed
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function getUserInfo($accessToken)
    {
        $graph = new Graph();
        $graph->setAccessToken($accessToken->getToken());
        $user = $graph->createRequest('GET', '/me')
            ->setReturnType(Model\User::class)
            ->execute();
        return $user;
    }
}